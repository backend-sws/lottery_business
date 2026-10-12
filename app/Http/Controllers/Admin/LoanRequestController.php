<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\LoanRequest;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanRequestController extends Controller
{
    /**
     * List all loan requests
     */
    public function index(Request $request)
    {
        $query = LoanRequest::with(['user', 'approvedLoan'])
            ->orderBy('created_at', 'desc');

        if ($request->has('status') && in_array($request->status, ['pending', 'approved', 'rejected'])) {
            $query->where('status', $request->status);
        }

        $requests = $query->get();

        return ApiResponse::success($requests, 'Loan requests retrieved successfully');
    }

    /**
     * Approve loan request and generate active loan with installments
     */
    public function approve(Request $request, $id)
    {
        $loanRequest = LoanRequest::with('user')->findOrFail($id);

        if ($loanRequest->status === 'approved') {
            return ApiResponse::error('This loan request has already been approved.', 422);
        }

        $validated = $request->validate([
            'interest_rate_percent' => 'nullable|numeric|min:0',
            'amount' => 'nullable|numeric|min:1',
            'duration_months' => 'nullable|integer|min:1',
            'payment_frequency' => 'nullable|in:daily,weekly,monthly',
            'admin_remarks' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $amount = $validated['amount'] ?? $loanRequest->amount;
            $durationMonths = $validated['duration_months'] ?? $loanRequest->duration_months;
            $frequency = $validated['payment_frequency'] ?? $loanRequest->payment_frequency;
            $rate = isset($validated['interest_rate_percent']) ? (float) $validated['interest_rate_percent'] : 12.0;

            // 1. Create Active Loan
            $loan = Loan::create([
                'user_id' => $loanRequest->user_id,
                'amount' => $amount,
                'interest_rate_percent' => $rate,
                'duration_months' => $durationMonths,
                'payment_frequency' => $frequency,
                'status' => 'active',
            ]);

            // 2. Generate Installments (EMI Engine)
            $P = (float) $loan->amount;
            $duration = (int) $loan->duration_months;

            if ($frequency === 'monthly') {
                $N = $duration;
                $R = ($rate / 12) / 100;
                $step = 'addMonth';
            } elseif ($frequency === 'weekly') {
                $N = $duration * 4;
                $R = ($rate / 52) / 100;
                $step = 'addWeek';
            } else {
                $N = $duration * 30;
                $R = ($rate / 365) / 100;
                $step = 'addDay';
            }

            $EMI = $R > 0
                ? ($P * $R * pow(1 + $R, $N)) / (pow(1 + $R, $N) - 1)
                : $P / $N;

            $balance = $P;
            $date = Carbon::today();

            for ($i = 1; $i <= $N; $i++) {
                $date = $date->copy()->$step();
                $interest = $balance * $R;
                $principal = $EMI - $interest;

                if ($i === $N) {
                    $principal = $balance;
                    $EMI = $principal + $interest;
                }

                LoanInstallment::create([
                    'loan_id' => $loan->id,
                    'due_date' => $date,
                    'total_amount' => round($EMI, 2),
                    'principal_component' => round($principal, 2),
                    'interest_component' => round($interest, 2),
                    'status' => 'pending',
                ]);

                $balance -= $principal;
            }

            // 3. Mark request as approved
            $loanRequest->update([
                'status' => 'approved',
                'approved_loan_id' => $loan->id,
                'admin_remarks' => $validated['admin_remarks'] ?? 'Loan approved by Admin',
            ]);

            DB::commit();

            return ApiResponse::success([
                'loan' => $loan,
                'loan_request' => $loanRequest,
            ], 'Loan approved successfully and active loan created.');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to approve loan: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Reject a loan request
     */
    public function reject(Request $request, $id)
    {
        $loanRequest = LoanRequest::findOrFail($id);

        if ($loanRequest->status === 'approved') {
            return ApiResponse::error('Cannot reject a loan that has already been approved.', 422);
        }

        $validated = $request->validate([
            'admin_remarks' => 'nullable|string|max:1000',
        ]);

        $loanRequest->update([
            'status' => 'rejected',
            'admin_remarks' => $validated['admin_remarks'] ?? 'Rejected by Admin',
        ]);

        return ApiResponse::success($loanRequest, 'Loan request rejected.');
    }
}
