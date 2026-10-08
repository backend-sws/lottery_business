<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Committee;
use App\Models\Installment;
use App\Models\AgentCollection;
use App\Models\AgentTarget;
use App\Http\Requests\MemberRequest;
use App\Helpers\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class MemberController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 📋 LIST MEMBERS
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $role = $request->input('role');
        $query = User::role($role ? (is_array($role) ? $role : [$role]) : ['member', 'agent'])
            ->with(['roles', 'loans', 'committees'])
            ->withCount(['committees', 'loans'])
            ->withSum(['installments as total_investment' => function($q) {
                $q->where('installments.status', 'paid');
            }], 'amount');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                // Active: has at least one active committee or active loan
                $query->where(function($q) {
                    $q->whereHas('committees', function($c) {
                        $c->where('committees.status', 'active');
                    })->orWhereHas('loans', function($l) {
                        $l->where('loans.status', 'active');
                    });
                });
            } elseif ($status === 'pending') {
                // Pending: missing verified phone, or missing aadhar/pan
                $query->where(function($q) {
                    $q->whereNull('aadhar_card')
                      ->orWhereNull('pan_card')
                      ->orWhereNull('id_proof');
                });
            } elseif ($status === 'inactive') {
                // Inactive: neither active nor pending
                $query->whereDoesntHave('committees')
                      ->whereDoesntHave('loans');
            }
        }

        // Community filter
        if ($request->filled('community')) {
            $community = $request->input('community');
            $query->whereHas('committees', function($q) use ($community) {
                if (is_numeric($community)) {
                    $q->where('committees.id', $community);
                } else {
                    $q->where('committees.name', 'like', "%{$community}%");
                }
            });
        }

        if ($request->ajax() || $request->has('draw')) {
            return DataTables::of($query)->make(true);
        }

        $perPage = min((int) $request->input('paginate', $request->input('per_page', 50)), 200);
        if ($perPage <= 0) $perPage = 50;
        $paginated = $query->paginate($perPage);
        
        $paginated->through(function ($user) {
            if ($user->hasRole('agent')) {
                $todayCollectionCount = AgentCollection::where('agent_id', $user->id)
                    ->whereDate('created_at', Carbon::today())
                    ->where('status', 'approved')
                    ->count();
                $user->status = $todayCollectionCount > 0 ? 'Active' : 'Offline';
            }
            return $user;
        });

        return ApiResponse::success(
            $paginated,
            'Members fetched successfully'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ➕ CREATE MEMBER / AGENT
    |--------------------------------------------------------------------------
    |
    */
    public function store(MemberRequest $request)
    {
        $this->authorize('create', User::class);

        $member = User::create($request->validated());

        $role = $request->input('role', 'member');
        $member->assignRole($role === 'agent' ? 'agent' : 'member');

        return ApiResponse::success(
            $member,
            'User created successfully'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 👁️ SHOW MEMBER
    |--------------------------------------------------------------------------
    */
    public function show($id)
    {
        $this->authorize('view', User::class);

        $user = User::with(['roles', 'committees', 'loans'])->findOrFail($id);

        return ApiResponse::success(
            $user,
            'User details fetched'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ✏️ UPDATE MEMBER
    |--------------------------------------------------------------------------
    */
    public function update(MemberRequest $request, $id)
    {
        $this->authorize('update', User::class);

        $member = User::findOrFail($id);
        $data = $request->validated();
        if (empty($data['password'])) {
            unset($data['password']);
        }
        $member->update($data);

        return ApiResponse::success(
            $member,
            'Member updated successfully'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ❌ DELETE MEMBER
    |--------------------------------------------------------------------------
    */
    public function destroy(Request $request, $id)
    {
        $this->authorize('delete', User::class);

        $member = User::findOrFail($id);
        $isAgent = $member->hasRole('agent');
        
        $force = filter_var($request->query('force', $request->input('force', false)), FILTER_VALIDATE_BOOLEAN);

        $hasAgentRecords = $isAgent && (
            AgentCollection::where('agent_id', $member->id)->exists() ||
            AgentTarget::where('agent_id', $member->id)->exists()
        );

        $hasFinancials = $member->installments()->exists() 
            || $member->loans()->exists() 
            || $member->payouts()->exists()
            || $hasAgentRecords;

        if ($hasFinancials && !$force) {
            $msg = $isAgent
                ? 'This agent has associated collection or target records. Pass force=true to delete.'
                : 'This member has existing financial records (installments, loans, or payouts). Pass force=true to delete.';

            return ApiResponse::error(
                $msg,
                422,
                ['has_financials' => true]
            );
        }

        DB::transaction(function () use ($member, $force) {
            // Detach joined committees
            $member->committees()->detach();

            // Unset collector from installments
            Installment::where('collected_by', $member->id)->update(['collected_by' => null]);
            if (class_exists(\App\Models\LoanInstallment::class)) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('loan_installments', 'collected_by')) {
                    \App\Models\LoanInstallment::where('collected_by', $member->id)->update(['collected_by' => null]);
                }
            }

            if ($force) {
                // Delete collections related to this user (as member or agent)
                AgentCollection::where('member_id', $member->id)
                    ->orWhere('agent_id', $member->id)
                    ->delete();
                
                // Delete targets
                AgentTarget::where('agent_id', $member->id)->delete();

                // Delete installments and loans
                $member->installments()->delete();
                foreach ($member->loans as $loan) {
                    $loan->installments()->delete();
                    $loan->delete();
                }
                $member->payouts()->delete();
                $member->notifications()->delete();
                if (class_exists(\App\Models\UserTransaction::class)) {
                    \App\Models\UserTransaction::where('user_id', $member->id)->delete();
                }
            } else {
                AgentTarget::where('agent_id', $member->id)->delete();
                AgentCollection::where('agent_id', $member->id)->delete();
            }

            $member->tokens()->delete();
            $member->delete();
        });

        return ApiResponse::success(
            null,
            $isAgent ? 'Agent deleted successfully' : 'Member deleted successfully'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | 🎓 ENROLL MEMBER IN COMMITTEE
    |--------------------------------------------------------------------------
    */
    public function enroll(Request $request, $id, \App\Services\NotificationService $notify)
    {
        $this->authorize('update', User::class);

        $request->validate([
            'committee_id' => 'required|exists:committees,id'
        ]);

        return DB::transaction(function () use ($request, $id, $notify) {

            $user = User::findOrFail($id);
            $committee = Committee::findOrFail($request->committee_id);

            // ❌ Only member allowed
            if (!$user->hasRole('member')) {
                return ApiResponse::error('Only members can be enrolled');
            }

            // ❌ Committee must be active
            if ($committee->status !== 'active') {
                return ApiResponse::error('Committee is not active');
            }

            // ❌ Already enrolled check
            if ($committee->members()->where('user_id', $user->id)->exists()) {
                return ApiResponse::error('User already enrolled');
            }

            // ✅ Attach member
            $committee->members()->syncWithoutDetaching([$user->id]);

            // 📅 Generate Installments
            $startDate = now();
            $endDate   = now()->addMonths($committee->duration);

            for ($i = 0; $i < 1000; $i++) {
                $dueDate = $startDate->copy();

                if ($committee->payment_frequency === 'daily') {
                    $dueDate->addDays($i);
                } elseif ($committee->payment_frequency === 'weekly') {
                    $dueDate->addWeeks($i);
                } else {
                    $dueDate->addMonths($i);
                }

                if ($dueDate->gte($endDate)) {
                    break;
                }

                Installment::firstOrCreate(
                    [
                        'user_id'       => $user->id,
                        'committee_id'  => $committee->id,
                        'due_date'      => $dueDate->format('Y-m-d'),
                    ],
                    [
                        'amount' => $committee->amount,
                        'status' => 'pending',
                    ]
                );
            }

            // 🔔 Send Notification
            $notify->sendNotification(
                $user,
                "Committee Joined",
                "You have been enrolled in {$committee->name}"
            );

            return ApiResponse::success(
                null,
                'Member enrolled successfully'
            );
        });
    }


    /*
    |--------------------------------------------------------------------------
    | 👤 IMPERSONATE USER (SECURE)
    |--------------------------------------------------------------------------
    */
    public function impersonate($id)
    {
        $this->authorize('loginAsUser', User::class);

        $user = User::findOrFail($id);

        // 🔒 Remove old tokens
        $user->tokens()->delete();

        // 🔑 Create short-lived token (30 min)
        $token = $user->createToken(
            'impersonation',
            ['*'],
            now()->addMinutes(30)
        )->plainTextToken;

        return ApiResponse::success([
            'token'        => $token,
            'user'         => $user,
            'redirect_url' => '/member/dashboard'
        ], 'Impersonation successful (30 min)');
    }


    /*
    |--------------------------------------------------------------------------
    | 🔑 CHANGE PASSWORD
    |--------------------------------------------------------------------------
    */
    public function changePassword(Request $request, $id)
    {
        $this->authorize('update', User::class);

        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::findOrFail($id);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return ApiResponse::success(
            null,
            'Password changed successfully'
        );
    }
}
