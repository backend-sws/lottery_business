<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ApiResponse;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\AgentCollection;
use App\Models\User;
use App\Models\UserTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    // 💳 Process Payment (Collection with Agent & Mode support)
    public function pay(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:committee,loan',
            'installment_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'agent_id' => 'nullable|integer',
            'payment_mode' => 'nullable|in:cash,qr,upi,online',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors()->first(), 422);
        }

        $user = $request->user();
        $type = $request->type;
        $installmentId = (int) $request->installment_id;
        $amount = (float) $request->amount;
        $agentId = $request->filled('agent_id') ? (int) $request->agent_id : null;
        $paymentMode = strtolower($request->input('payment_mode', 'cash'));

        // Lookup agent if provided
        $agent = null;
        if ($agentId) {
            $agent = User::find($agentId);
        }

        DB::beginTransaction();

        try {
            $description = '';
            $referenceType = null;
            $referenceId = null;

            if ($type === 'committee') {
                $installment = null;

                // Strategy 1: Look up by ID belonging directly to user
                if ($installmentId > 0) {
                    $installment = Installment::where('id', $installmentId)
                        ->where('user_id', $user->id)
                        ->first();
                }

                // Strategy 2: Check if $installmentId is actually a committee_id
                if (!$installment && $installmentId > 0) {
                    $installment = Installment::where('committee_id', $installmentId)
                        ->where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->orderBy('due_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->first();
                }

                // Strategy 3: Check by direct ID if committee belongs to user
                if (!$installment && $installmentId > 0) {
                    $candidate = Installment::find($installmentId);
                    if ($candidate && (int)$candidate->user_id === (int)$user->id) {
                        $installment = $candidate;
                    }
                }

                // Strategy 4: Fallback to next pending committee installment for user
                if (!$installment) {
                    $installment = Installment::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->orderBy('due_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->first();
                }

                if (!$installment) {
                    return ApiResponse::error('No pending committee installment found to pay.', 404);
                }

                if ($installment->status === 'paid') {
                    return ApiResponse::error('Committee installment is already paid.', 400);
                }

                $updateData = [
                    'status' => 'paid',
                    'paid_date' => now()
                ];
                if ($agentId && \Illuminate\Support\Facades\Schema::hasColumn('installments', 'collected_by')) {
                    $updateData['collected_by'] = $agentId;
                }
                $installment->update($updateData);

                // Check if all committee installments for this user are completed
                if ($installment->committee_id) {
                    $pendingCount = Installment::where('user_id', $user->id)
                        ->where('committee_id', $installment->committee_id)
                        ->where('status', 'pending')
                        ->count();

                    if ($pendingCount === 0) {
                        DB::table('committee_user')
                            ->where('committee_id', $installment->committee_id)
                            ->where('user_id', $user->id)
                            ->update(['status' => 'completed']);
                    }
                }

                $referenceType = Installment::class;
                $referenceId = $installment->id;

            } else {
                $installment = null;

                // Strategy 1: Look up by installment ID belonging to user's loan
                if ($installmentId > 0) {
                    $installment = LoanInstallment::where('id', $installmentId)
                        ->whereHas('loan', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->first();
                }

                // Strategy 2: Candidate direct lookup by ID (verifying loan ownership)
                if (!$installment && $installmentId > 0) {
                    $candidate = LoanInstallment::with('loan')->find($installmentId);
                    if ($candidate && $candidate->loan && (int)$candidate->loan->user_id === (int)$user->id) {
                        $installment = $candidate;
                    }
                }

                // Strategy 3: Check if $installmentId was passed as loan_id
                if (!$installment && $installmentId > 0) {
                    $loan = Loan::where('id', $installmentId)
                        ->where('user_id', $user->id)
                        ->first();

                    if ($loan) {
                        $installment = LoanInstallment::where('loan_id', $loan->id)
                            ->where('status', 'pending')
                            ->orderBy('due_date', 'asc')
                            ->orderBy('id', 'asc')
                            ->first();
                    }
                }

                // Strategy 4: Find next pending installment from user's active loan
                if (!$installment) {
                    $activeLoan = Loan::where('user_id', $user->id)
                        ->where('status', 'active')
                        ->latest()
                        ->first();

                    if ($activeLoan) {
                        $installment = LoanInstallment::where('loan_id', $activeLoan->id)
                            ->where('status', 'pending')
                            ->orderBy('due_date', 'asc')
                            ->orderBy('id', 'asc')
                            ->first();
                    }
                }

                // Strategy 5: Any pending installment for any loan belonging to this user
                if (!$installment) {
                    $installment = LoanInstallment::whereHas('loan', function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        })
                        ->where('status', 'pending')
                        ->orderBy('due_date', 'asc')
                        ->orderBy('id', 'asc')
                        ->first();
                }

                if (!$installment) {
                    return ApiResponse::error('No pending loan installment found to pay.', 404);
                }

                if ($installment->status === 'paid') {
                    return ApiResponse::error('Loan installment is already paid.', 400);
                }

                $updateData = [
                    'status' => 'paid',
                    'paid_date' => now()
                ];
                if ($agentId && \Illuminate\Support\Facades\Schema::hasColumn('loan_installments', 'collected_by')) {
                    $updateData['collected_by'] = $agentId;
                }
                $installment->update($updateData);

                // Check if all installments for this loan are completed
                if ($installment->loan_id) {
                    $pendingLoanCount = LoanInstallment::where('loan_id', $installment->loan_id)
                        ->where('status', 'pending')
                        ->count();

                    if ($pendingLoanCount === 0) {
                        Loan::where('id', $installment->loan_id)->update(['status' => 'paid']);
                    }
                }

                $referenceType = LoanInstallment::class;
                $referenceId = $installment->id;
            }

            $effectiveAmount = $amount > 0 ? $amount : (float)($installment->total_amount ?? $installment->amount ?? 0);
            $modeLabel = strtoupper($paymentMode);
            $agentLabel = $agent ? " (Agent: {$agent->name})" : "";
            $description = "Paid {$type} installment #{$installment->id} via {$modeLabel}{$agentLabel}";

            // 1. Record Agent Collection if agent selected
            if ($agentId) {
                try {
                    AgentCollection::create([
                        'agent_id' => $agentId,
                        'member_id' => $user->id,
                        'collection_type' => $type,
                        'amount_collected' => $effectiveAmount,
                        'payment_mode' => $paymentMode,
                        'status' => 'approved',
                        'loan_installment_id' => $type === 'loan' ? $installment->id : null,
                        'installment_id' => $type === 'committee' ? $installment->id : null,
                        'collected_at' => now(),
                        'details' => "Customer EMI payment via {$modeLabel} through Agent #{$agentId}",
                    ]);
                } catch (\Exception $e) {
                    // Non-fatal if agent_collections table differs slightly
                }
            }

            // 2. Create User Transaction (Log member payment)
            \App\Models\UserTransaction::create([
                'user_id' => $user->id,
                'type' => 'credit',
                'amount' => $effectiveAmount,
                'description' => $description,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
            ]);

            DB::commit();

            return ApiResponse::success([
                'installment_id' => $installment->id,
                'status' => 'paid',
                'payment_mode' => $paymentMode,
                'agent_id' => $agentId,
                'agent_name' => $agent ? $agent->name : null,
                'description' => $description,
            ], 'Payment successful');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Payment failed: ' . $e->getMessage(), 500);
        }
    }
}
