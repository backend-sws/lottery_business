<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\User;
use App\Models\Committee;
use App\Models\Payout;
use App\Http\Requests\InstallmentRequest;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InstallmentController extends Controller
{
    // 💰 Collect Payment (SECURE VERSION)
    public function collect(InstallmentRequest $request, NotificationService $notify)
    {
        // 🔐 Authorization check
        if (!$request->user()->hasRole('Super Admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = $request->validated();

        DB::beginTransaction();

        try {
            // Find pending installment
            $installment = Installment::where('user_id', $data['user_id'])
                ->where('committee_id', $data['committee_id'])
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();

            if (!$installment) {
                return response()->json([
                    'status' => false,
                    'message' => 'No pending installments found'
                ], 400);
            }

            // Update installment
            $installment->update([
                'amount' => $data['amount'],
                'status' => 'paid',
                'paid_date' => $data['paid_date'] ?? now(),
                'collected_by' => $request->user()->id
            ]);

            // Create User Transaction (Log member payment)
            \App\Models\UserTransaction::create([
                'user_id' => $installment->user_id,
                'type' => 'credit',
                'amount' => $data['amount'],
                'description' => "Paid committee installment #{$installment->id}",
                'reference_type' => \App\Models\Installment::class,
                'reference_id' => $installment->id,
            ]);

            // Get user
            $user = User::findOrFail($data['user_id']);

            // 🔔 Notification (safe)
            try {
                $notify->sendNotification(
                    $user,
                    "Payment Received",
                    "₹{$installment->amount} received successfully"
                );
            } catch (\Exception $e) {}

            // Check pending
            $pendingCount = Installment::where('user_id', $data['user_id'])
                ->where('committee_id', $data['committee_id'])
                ->where('status', 'pending')
                ->count();

            if ($pendingCount === 0) {

                $committee = Committee::findOrFail($data['committee_id']);

                $totalDeposits = Installment::where('user_id', $data['user_id'])
                    ->where('committee_id', $data['committee_id'])
                    ->where('status', 'paid')
                    ->sum('amount');

                $returnPercentage = $committee->return_percentage ?? 0;
                $returnAmount = ($totalDeposits * $returnPercentage) / 100;
                $totalPayout = $totalDeposits + $returnAmount;

                // Create payout
                Payout::firstOrCreate(
                    [
                        'user_id' => $data['user_id'],
                        'committee_id' => $data['committee_id']
                    ],
                    [
                        'total_deposits' => $totalDeposits,
                        'return_amount' => $returnAmount,
                        'total_payout' => $totalPayout,
                        'status' => 'pending'
                    ]
                );

                // Final Notification
                try {
                    $notify->sendNotification(
                        $user,
                        "Committee Completed",
                        "Total ₹{$totalPayout} payout processing"
                    );
                } catch (\Exception $e) {}

                // Update pivot
                DB::table('committee_user')
                    ->where('committee_id', $data['committee_id'])
                    ->where('user_id', $data['user_id'])
                    ->update(['status' => 'completed']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Payment collected successfully',
                'data' => $installment
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ⚠️ Send Due Warnings
    public function sendWarnings(NotificationService $notify)
    {
        $count = 0;

        Installment::with('user')
            ->where('status', 'pending')
            ->where('due_date', '<', now())
            ->chunk(100, function ($installments) use ($notify, &$count) {
                foreach ($installments as $installment) {
                    if ($installment->user) {
                        try {
                            $notify->sendNotification(
                                $installment->user,
                                "Payment Overdue",
                                "₹{$installment->amount} overdue"
                            );
                            $count++;
                        } catch (\Exception $e) {}
                    }
                }
            });

        return response()->json([
            'status' => true,
            'message' => "$count warnings sent"
        ]);
    }

    // ⏰ Reminders
    public function sendPaymentReminders(NotificationService $notify)
    {
        $count = 0;

        Installment::with('user')
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(3)])
            ->chunk(100, function ($installments) use ($notify, &$count) {
                foreach ($installments as $installment) {
                    if ($installment->user) {
                        try {
                            $notify->sendNotification(
                                $installment->user,
                                "Reminder",
                                "₹{$installment->amount} due soon"
                            );
                            $count++;
                        } catch (\Exception $e) {}
                    }
                }
            });

        return response()->json([
            'status' => true,
            'message' => "$count reminders sent"
        ]);
    }

    // 📋 Pending List
    public function pending(\Illuminate\Http\Request $request)
    {
        $query = Installment::where('status', 'pending');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('committee_id')) {
            $query->where('committee_id', $request->committee_id);
        }

        return response()->json([
            'status' => true,
            'data' => $query->orderBy('due_date', 'asc')->get()
        ]);
    }

    // 📋 List
    public function index()
    {
        return Installment::with(['user', 'committee'])
            ->latest()
            ->paginate(20);
    }

    // 👁️ Show
    public function show($id)
    {
        return Installment::with(['user', 'committee'])->findOrFail($id);
    }

    // ✏️ Update
    public function update(InstallmentRequest $request, $id)
    {
        $installment = Installment::findOrFail($id);
        $installment->update($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Updated',
            'data' => $installment
        ]);
    }

    // ❌ Delete
    public function destroy($id)
    {
        $user = auth()->user() ?? request()->user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        DB::beginTransaction();

        try {
            $installment = Installment::findOrFail($id);

            // Clean up related agent collections
            \App\Models\AgentCollection::where('installment_id', $installment->id)->delete();

            // Clean up user transactions
            if (class_exists(\App\Models\UserTransaction::class)) {
                \App\Models\UserTransaction::where('reference_type', \App\Models\Installment::class)
                    ->where('reference_id', $installment->id)
                    ->delete();
            }

            $installment->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Installment deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete installment: ' . $e->getMessage()
            ], 500);
        }
    }
}