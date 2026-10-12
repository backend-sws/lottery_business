<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoanRequest;
use App\Helpers\ApiResponse;

class LoanRequestController extends Controller
{
    /**
     * List user's loan applications
     */
    public function index(Request $request)
    {
        $requests = LoanRequest::where('user_id', $request->user()->id)
            ->with('approvedLoan')
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponse::success($requests, 'Loan applications fetched successfully');
    }

    /**
     * Submit a new loan request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:500',
            'duration_months' => 'required|integer|min:1|max:120',
            'payment_frequency' => 'required|in:daily,weekly,monthly',
            'purpose' => 'nullable|string|max:500',
        ]);

        // Check if there is already a pending application
        $pending = LoanRequest::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->first();

        if ($pending) {
            return ApiResponse::error('You already have a loan application currently under review.', 422);
        }

        $loanRequest = LoanRequest::create([
            'user_id' => $request->user()->id,
            'amount' => $validated['amount'],
            'duration_months' => $validated['duration_months'],
            'payment_frequency' => $validated['payment_frequency'],
            'purpose' => $validated['purpose'] ?? null,
            'status' => 'pending',
        ]);

        return ApiResponse::success($loanRequest, 'Loan application submitted successfully. We will review it shortly.');
    }
}
