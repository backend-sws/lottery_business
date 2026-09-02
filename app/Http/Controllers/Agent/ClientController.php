<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\User;
use App\Models\Loan;
use App\Models\AgentCollection;
use Carbon\Carbon;

class ClientController extends Controller
{
    // 👥 Get Managed Clients (My Clients Screen)
    public function index(Request $request)
    {
        $agentId = $request->user()->id;
        $clientsQuery = User::role('member');

        $totalClients = clone $clientsQuery;
        $totalClientsCount = $totalClients->count();

        // Get count of active loans for these clients using a subquery (Prevent Memory Exhaustion)
        $activeLoansCount = Loan::whereHas('user', function ($q) {
            $q->role('member');
        })->where('status', 'active')->count();

        // Get Recent Clients Activity (Figma: Sort By Name)
        $search = $request->input('search');
        if ($search) {
            $clientsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
                if (is_numeric($search)) {
                    $q->orWhere('id', $search);
                }
            });
        }

        $recentClients = $clientsQuery->orderBy('name', 'asc')
            ->paginate(15)
            ->through(function ($client) use ($agentId) {
                // Find last collection for this client by this agent
                $lastCollection = AgentCollection::where('agent_id', $agentId)
                    ->where('member_id', $client->id)
                    ->where('status', 'approved')
                    ->orderBy('created_at', 'desc')
                    ->first();

                return [
                    'id' => $client->id,
                    'name' => $client->name,
                    'status' => 'Active', // Or determine overdue status based on installments
                    'last_collection_amount' => $lastCollection ? $lastCollection->amount_collected : 0,
                    'last_collection_date' => $lastCollection ? $lastCollection->created_at->format('Y-m-d') : null,
                ];
            });

        return ApiResponse::success([
            'total_clients_managed' => $totalClientsCount,
            'active_loans' => $activeLoansCount,
            'recent_clients' => $recentClients
        ], 'My clients fetched');
    }

    // 📄 Single Client Profile
    public function show(Request $request, $id)
    {
        $agentId = $request->user()->id;

        $client = User::where('id', $id)
            ->role('member')
            ->firstOrFail();

        // Personal Details
        $personalDetails = [
            'name' => $client->name,
            'client_id' => $client->id,
            'phone' => $client->phone,
            'email' => $client->email,
            'address' => $client->address,
            'aadhar_card' => $client->aadhar_card ? url("/api/documents/kyc/{$client->id}/" . basename($client->aadhar_card)) : null,
            'id_proof' => $client->id_proof ? url("/api/documents/kyc/{$client->id}/" . basename($client->id_proof)) : null,
            'pan_card' => $client->pan_card ? url("/api/documents/kyc/{$client->id}/" . basename($client->pan_card)) : null,
        ];

        // Active Loans
        $activeLoans = Loan::where('user_id', $client->id)
            ->where('status', 'active')
            ->get()
            ->map(function ($loan) {
                // Calculate paid amount and outstanding
                $totalPaid = $loan->installments()->where('status', 'paid')->sum('total_amount');
                $totalOutstanding = $loan->amount + ($loan->amount * ($loan->interest_rate_percent / 100)) - $totalPaid;
                
                return [
                    'loan_id' => $loan->id,
                    'total_loan_amount' => $loan->amount,
                    'outstanding' => $totalOutstanding > 0 ? $totalOutstanding : 0,
                    'total_paid' => $totalPaid,
                    'emi_progress' => 'in_progress',
                ];
            });

        // Recent Transactions (Collections made from this client)
        $recentTransactions = AgentCollection::where('member_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'type' => $item->collection_type === 'committee' ? 'Committee Payment' : 'EMI Payment',
                    'amount' => $item->amount_collected,
                    'date' => $item->created_at->format('d M Y H:i A'),
                    'status' => $item->status === 'approved' ? 'Success' : 'Pending',
                ];
            });

        return ApiResponse::success([
            'personal_details' => $personalDetails,
            'active_loans' => $activeLoans,
            'recent_transactions' => $recentTransactions
        ], 'Client profile fetched');
    }

    // 👮 Submit Client KYC Documents
    public function submitKyc(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'aadhar_front' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'aadhar_back' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'pan_front' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $userId = $request->input('user_id');

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $user = User::role('member')->findOrFail($userId);

            // Handle Aadhaar Front
            if ($request->hasFile('aadhar_front')) {
                if ($user->aadhar_card) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($user->aadhar_card);
                }
                $user->aadhar_card = $request->file('aadhar_front')->store("kyc/{$user->id}", 'local');
            }

            // Handle Aadhaar Back
            if ($request->hasFile('aadhar_back')) {
                if ($user->id_proof && $user->id_proof !== $user->aadhar_card) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($user->id_proof);
                }
                $user->id_proof = $request->file('aadhar_back')->store("kyc/{$user->id}", 'local');
            } elseif ($request->hasFile('aadhar_front') && !$user->id_proof) {
                // fallback to front image if back is not uploaded
                $user->id_proof = $user->aadhar_card;
            }

            // Handle PAN Front
            if ($request->hasFile('pan_front')) {
                if ($user->pan_card) {
                    \Illuminate\Support\Facades\Storage::disk('local')->delete($user->pan_card);
                }
                $user->pan_card = $request->file('pan_front')->store("kyc/{$user->id}", 'local');
            }

            $user->save();

            \Illuminate\Support\Facades\DB::commit();

            return ApiResponse::success([
                'user_id' => $user->id,
                'aadhar_front_url' => $user->aadhar_card ? url("/api/documents/kyc/{$user->id}/" . basename($user->aadhar_card)) : null,
                'aadhar_back_url' => $user->id_proof ? url("/api/documents/kyc/{$user->id}/" . basename($user->id_proof)) : null,
                'pan_front_url' => $user->pan_card ? url("/api/documents/kyc/{$user->id}/" . basename($user->pan_card)) : null,
            ], 'KYC documents submitted successfully');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return ApiResponse::error('KYC upload failed: ' . $e->getMessage(), 500);
        }
    }

    // ➕ Add New Client / Member by Agent
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'address' => 'nullable|string',
        ]);

        $agentId = $request->user()->id;

        // Auto-generate email if not provided
        $email = !empty($validated['email']) ? $validated['email'] : ($validated['phone'] . '@janta.community');

        // Check if generated email is taken
        if (User::where('email', $email)->exists()) {
            $email = $validated['phone'] . '_' . time() . '@janta.community';
        }

        // Default password is their phone number
        $password = \Illuminate\Support\Facades\Hash::make($validated['phone']);

        $client = User::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $email,
            'address' => $validated['address'] ?? null,
            'password' => $password,
            'agent_id' => $agentId,
            'role' => 'member',
        ]);

        $client->assignRole('member');

        return ApiResponse::success($client, 'Client created successfully');
    }

}
