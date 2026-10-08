<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\User;
use App\Helpers\ApiResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class LoanController extends Controller
{
    public function store(Request $request)
    {
        // 🔐 Authorization
        if (!$request->user()->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        // ✅ Validation
        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',

            'name' => 'required_without:user_id|string|max:255',
            'email' => [
                'required_without:user_id',
                'email',
                Rule::unique('users')->ignore($request->user_id)
            ],
            'phone' => [
                'required_without:user_id',
                Rule::unique('users')->ignore($request->user_id)
            ],
            'password' => 'required_without:user_id|min:6',
            'address' => 'required_without:user_id|string',

            'amount' => 'required|numeric|min:1',
            'interest_rate_percent' => 'required|numeric|min:0',
            'duration_months' => 'required|integer|min:1',
            'payment_frequency' => 'required|in:daily,weekly,monthly',
        ]);

        DB::beginTransaction();

        try {
            // 👤 User Handle
            if (!empty($data['user_id'])) {
                $user = User::findOrFail($data['user_id']);
            } else {
                $user = User::create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                    'password' => Hash::make($data['password']),
                    'address' => $data['address'],
                    'is_phone_verified' => true,
                ]);
                $user->assignRole('member');
            }

            // 💰 Create Loan
            $loan = Loan::create([
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'interest_rate_percent' => $data['interest_rate_percent'],
                'duration_months' => $data['duration_months'],
                'payment_frequency' => $data['payment_frequency'],
                'status' => 'active',
            ]);

            // 📊 EMI Calculation
            $P = (float) $loan->amount;
            $rate = (float) $loan->interest_rate_percent;
            $duration = (int) $loan->duration_months;
            $frequency = $loan->payment_frequency;

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

            DB::commit();

            return ApiResponse::success($loan, 'Loan created successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    // 💳 Collect Payment (SECURE)
    public function collect(Request $request, $id)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        DB::beginTransaction();

        try {
            $installment = LoanInstallment::findOrFail($id);

            if ($installment->status === 'paid') {
                return ApiResponse::error('Already paid', 400);
            }

            $installment->update([
                'status' => 'paid',
                'paid_date' => now(),
            ]);

            // Create User Transaction (Log member payment)
            \App\Models\UserTransaction::create([
                'user_id' => $installment->loan->user_id,
                'type' => 'credit',
                'amount' => $installment->total_amount,
                'description' => "Paid loan installment #{$installment->id}",
                'reference_type' => \App\Models\LoanInstallment::class,
                'reference_id' => $installment->id,
            ]);

            $pending = LoanInstallment::where('loan_id', $installment->loan_id)
                ->where('status', 'pending')
                ->count();

            if ($pending === 0) {
                $installment->loan->update(['status' => 'paid']);
            }

            DB::commit();

            return ApiResponse::success($installment, 'Collected');

        } catch (\Exception $e) {

            DB::rollBack();

            return ApiResponse::error('Failed', 500);
        }
    }

    public function index(Request $request)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $loans = Loan::with('user')->latest()->get();

        return ApiResponse::success($loans, 'Loans retrieved successfully');
    }

    public function show(Request $request, $id)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $loan = Loan::with(['user', 'installments'])->findOrFail($id);

        return ApiResponse::success($loan, 'Loan retrieved successfully');
    }

    public function installments(Request $request)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        $installments = LoanInstallment::with(['loan.user'])->latest()->get();

        return ApiResponse::success($installments, 'Installments retrieved successfully');
    }

    public function destroyInstallment(Request $request, $id)
    {
        $user = $request->user() ?? auth()->user();
        if (!$user || !$user->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        DB::beginTransaction();

        try {
            $installment = LoanInstallment::findOrFail($id);

            // Clean up related agent collections
            \App\Models\AgentCollection::where('loan_installment_id', $installment->id)->delete();

            // Clean up related user transaction if any
            if (class_exists(\App\Models\UserTransaction::class)) {
                \App\Models\UserTransaction::where('reference_type', \App\Models\LoanInstallment::class)
                    ->where('reference_id', $installment->id)
                    ->delete();
            }

            $installment->delete();

            DB::commit();

            return ApiResponse::success(null, 'Loan installment deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete loan installment: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        if (!$request->user()->hasRole('Super Admin')) {
            return ApiResponse::error('Unauthorized', 403);
        }

        DB::beginTransaction();

        try {
            $loan = Loan::findOrFail($id);

            // Delete all associated installments first
            $loan->installments()->delete();

            // Delete the loan
            $loan->delete();

            DB::commit();

            return ApiResponse::success(null, 'Loan and its installments deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponse::error('Failed to delete loan: ' . $e->getMessage(), 500);
        }
    }
}