<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\Installment;
use App\Models\Payout;
use App\Models\User;
use App\Models\Committee;
use App\Models\Loan;
use App\Models\LoanInstallment;

use App\Helpers\ApiResponse;

class AccountingController extends Controller
{
    /**
     * MEMBER LEDGER
     */
    public function memberLedger($userId)
    {
        $user = User::findOrFail($userId);

        $entries = JournalEntry::where(function ($q) use ($userId) {
                $q->where('reference_type', Installment::class)
                  ->whereIn('reference_id', function ($sub) use ($userId) {
                      $sub->select('id')->from('installments')->where('user_id', $userId);
                  });
            })
            ->orWhere(function ($q) use ($userId) {
                $q->where('reference_type', Payout::class)
                  ->whereIn('reference_id', function ($sub) use ($userId) {
                      $sub->select('id')->from('payouts')->where('user_id', $userId);
                  });
            })
            ->with('account')
            ->orderBy('transaction_date')
            ->get();

        return ApiResponse::success([
            'member' => $user->name,
            'entries' => $entries
        ]);
    }

    /**
     * COMMITTEE LEDGER
     */
    public function committeeLedger($committeeId)
    {
        $committee = Committee::findOrFail($committeeId);

        $entries = JournalEntry::where(function ($q) use ($committeeId) {
                $q->where('reference_type', Installment::class)
                  ->whereIn('reference_id', function ($sub) use ($committeeId) {
                      $sub->select('id')->from('installments')->where('committee_id', $committeeId);
                  });
            })
            ->orWhere(function ($q) use ($committeeId) {
                $q->where('reference_type', Payout::class)
                  ->whereIn('reference_id', function ($sub) use ($committeeId) {
                      $sub->select('id')->from('payouts')->where('committee_id', $committeeId);
                  });
            })
            ->with('account')
            ->orderBy('transaction_date')
            ->get();

        return ApiResponse::success([
            'committee' => $committee->name,
            'entries' => $entries
        ]);
    }

    /**
     * PROFIT & LOSS
     */
    public function profitAndLoss()
    {
        $revenueAccounts = Account::where('type', 'revenue')
            ->withSum('journalEntries', 'credit')
            ->get();

        $expenseAccounts = Account::where('type', 'expense')
            ->withSum('journalEntries', 'debit')
            ->get();

        $totalRevenue = $revenueAccounts->sum('journal_entries_sum_credit');
        $totalExpense = $expenseAccounts->sum('journal_entries_sum_debit');

        return ApiResponse::success([
            'revenue' => $revenueAccounts,
            'total_revenue' => $totalRevenue,
            'expenses' => $expenseAccounts,
            'total_expense' => $totalExpense,
            'net_profit' => $totalRevenue - $totalExpense
        ]);
    }

    /**
     * BALANCE SHEET
     */
    public function balanceSheet()
    {
        // COMMITTEE
        $commCashCollected = Installment::where('status', 'paid')->sum('amount');
        $commPendingReceivables = Installment::where('status', 'pending')->sum('amount');
        $commPayoutsDisbursed = Payout::where('status', 'paid')->sum('total_payout');
        $commPendingPayouts = Payout::where('status', 'pending')->sum('total_payout');

        // LOAN
        $loanPrincipalDisbursed = Loan::whereIn('status', ['active', 'paid'])->sum('amount');
        $loanCashCollected = LoanInstallment::where('status', 'paid')->sum('total_amount');

        $loanPrincipalPending = LoanInstallment::where('status', 'pending')->sum('principal_component');
        $loanInterestPending = LoanInstallment::where('status', 'pending')->sum('interest_component');

        $loanInterestCollected = LoanInstallment::where('status', 'paid')->sum('interest_component');

        // LOAN BREAKDOWN (OPTIMIZED)
        $loans = Loan::with(['user', 'installments'])->get()->map(function ($loan) {

            $paid = $loan->installments->where('status', 'paid');

            return [
                'id' => $loan->id,
                'user_name' => optional($loan->user)->name,
                'principal' => $loan->amount,
                'interest_rate' => $loan->interest_rate_percent . '%',
                'total_expected_interest' => $loan->installments->sum('interest_component'),
                'recovered_interest' => $paid->sum('interest_component'),
                'total_expected_return' => $loan->installments->sum('total_amount'),
                'total_recovered' => $paid->sum('total_amount'),
                'status' => $loan->status
            ];
        });

        $commAssetsTotal = max(0, $commCashCollected - $commPayoutsDisbursed) + $commPendingReceivables;
        $loanAssetsTotal = $loanCashCollected + $loanPrincipalPending + $loanInterestPending;

        return ApiResponse::success([
            'committee' => [
                'total_assets' => $commAssetsTotal,
                'total_liabilities' => $commPendingPayouts,
                'assets' => [
                    ['name' => 'Cash', 'balance' => max(0, $commCashCollected - $commPayoutsDisbursed)],
                    ['name' => 'Receivables', 'balance' => $commPendingReceivables],
                ],
                'liabilities' => [
                    ['name' => 'Pending Payouts', 'balance' => $commPendingPayouts],
                ],
                'equity' => [],
            ],
            'loan' => [
                'total_assets' => $loanAssetsTotal,
                'total_liabilities' => $loanPrincipalDisbursed,
                'assets' => [
                    ['name' => 'Collected', 'balance' => $loanCashCollected],
                    ['name' => 'Pending Principal', 'balance' => $loanPrincipalPending],
                    ['name' => 'Pending Interest', 'balance' => $loanInterestPending],
                ],
                'liabilities' => [
                    ['name' => 'Principal Given', 'balance' => $loanPrincipalDisbursed],
                ],
                'equity' => [
                    ['name' => 'Profit', 'balance' => $loanInterestCollected],
                ],
                'breakdown' => $loans
            ]
        ]);
    }
}