<?php

namespace App\Services;

use App\Models\User;
use App\Models\Committee;
use App\Models\Installment;
use App\Models\Loan;
use App\Models\LoanInstallment;
use App\Models\AgentCollection;
use App\Models\AgentTarget;
use App\Models\Material;
use App\Models\Lottery;
use App\Models\Payout;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    // 📊 Main Dashboard Stats (Optimized with short 5s debounce cache)
    public function getStats()
    {
        return Cache::remember('admin_dashboard_stats', 5, function () {
            $today = Carbon::today();
            $totalMembers = User::role('member')->count();
        $totalAgents = User::role('agent')->count();
        
        // Dynamic KYC Compliance
        $kycCompleted = User::role('member')->where(function($q) {
            $q->whereNotNull('aadhar_card')
              ->whereNotNull('pan_card')
              ->whereNotNull('id_proof');
        })->count();
        $kycRate = $totalMembers > 0 ? round(($kycCompleted / $totalMembers) * 100) : 0;

        // Active vs Pending vs Inactive Members
        $activeMembers = User::role('member')->where(function($q) {
            $q->whereHas('committees', function($c) {
                $c->where('committees.status', 'active');
            })->orWhereHas('loans', function($l) {
                $l->where('loans.status', 'active');
            });
        })->count();

        $pendingMembers = User::role('member')->where(function($q) {
            $q->whereNull('aadhar_card')
              ->orWhereNull('pan_card')
              ->orWhereNull('id_proof');
        })->count();

        $inactiveMembers = max(0, $totalMembers - $activeMembers);

        // Total Collections (Paid Installments & Paid Loan Installments)
        $installmentColl = (float) Installment::where('status', 'paid')->sum('amount');
        $loanColl = (float) LoanInstallment::where('status', 'paid')->sum('total_amount');
        $totalCollSum = $installmentColl + $loanColl;

        // Total Disbursements (Principal sum of all active loans + completed payouts)
        $loanDisbursedSum = (float) Loan::where('status', 'active')->sum('amount');
        $payoutsSum = (float) Payout::where('status', 'paid')->sum('total_payout');
        $totalDisbursedSum = $loanDisbursedSum + $payoutsSum;

        // Formatting helpers
        $totalDisbursedFormatted = $this->formatAmountToCrOrLakh($totalDisbursedSum);
        $totalCollectionsFormatted = $this->formatAmountToCrOrLakh($totalCollSum);

        // Fetch recent transactions dynamically from real DB
        $recentTx = collect();
        
        $paidInstallments = Installment::with(['user', 'committee'])
            ->where('status', 'paid')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        foreach ($paidInstallments as $inst) {
            $recentTx->push([
                'name' => $inst->user->name ?? ('User #' . $inst->user_id),
                'reference_id' => '#INST-' . $inst->id,
                'type' => 'Committee (' . ($inst->committee->name ?? 'Plan') . ')',
                'amount' => number_format($inst->amount, 2),
                'status' => 'Success',
                'timestamp' => $inst->updated_at ?? $inst->created_at
            ]);
        }

        $paidLoans = LoanInstallment::with(['loan.user'])
            ->where('status', 'paid')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        foreach ($paidLoans as $pl) {
            $recentTx->push([
                'name' => $pl->loan->user->name ?? 'Borrower',
                'reference_id' => '#LN-INST-' . $pl->id,
                'type' => 'Loan Repayment',
                'amount' => number_format($pl->total_amount, 2),
                'status' => 'Success',
                'timestamp' => $pl->updated_at ?? $pl->created_at
            ]);
        }

        // Pending installments for recent transactions preview
        $pendingInstallments = Installment::with(['user', 'committee'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get();

        foreach ($pendingInstallments as $inst) {
            $recentTx->push([
                'name' => $inst->user->name ?? ('User #' . $inst->user_id),
                'reference_id' => '#INST-' . $inst->id,
                'type' => 'Due Installment',
                'amount' => number_format($inst->amount, 2),
                'status' => 'Pending',
                'timestamp' => $inst->created_at
            ]);
        }

        // Sort by timestamp desc and take 5
        $recentTx = $recentTx->sortByDesc('timestamp')->values()->take(5);

        // Monthly trends query: Dynamic last 6 months
        $monthlyTrends = [];
        for ($m = 5; $m >= 0; $m--) {
            $monthDate = Carbon::now()->subMonths($m);
            $monthName = $monthDate->format('M');
            $monthYear = $monthDate->year;
            $monthNum = $monthDate->month;

            $instSum = (float) Installment::where('status', 'paid')
                ->whereYear('paid_date', $monthYear)
                ->whereMonth('paid_date', $monthNum)
                ->sum('amount');
            $loanSum = (float) LoanInstallment::where('status', 'paid')
                ->whereYear('paid_date', $monthYear)
                ->whereMonth('paid_date', $monthNum)
                ->sum('total_amount');
            
            $monthTotal = $instSum + $loanSum;
            $monthlyTrends[] = [
                'month' => $monthName,
                'total' => round($monthTotal / 100000, 2), // in Lakhs
                'raw_amount' => $monthTotal
            ];
        }

        // ===== DYNAMIC COLLECTION METRICS =====
        $todayCollectionApproved = (float) AgentCollection::whereDate('created_at', $today)
            ->where('status', 'approved')->sum('amount_collected');
        $todayCollectionAll = (float) AgentCollection::whereDate('created_at', $today)
            ->sum('amount_collected');

        // Include today's paid installments and loan installments
        $todayInstPaid = (float) Installment::whereDate('paid_date', $today)->where('status', 'paid')->sum('amount');
        $todayLoanPaid = (float) LoanInstallment::whereDate('paid_date', $today)->where('status', 'paid')->sum('total_amount');
        $todayTotalPaid = $todayCollectionApproved + $todayInstPaid + $todayLoanPaid;

        // Yesterday's collection for comparison
        $yesterday = Carbon::yesterday();
        $yesterdayColl = (float) (AgentCollection::whereDate('created_at', $yesterday)->where('status', 'approved')->sum('amount_collected')
            + Installment::whereDate('paid_date', $yesterday)->where('status', 'paid')->sum('amount')
            + LoanInstallment::whereDate('paid_date', $yesterday)->where('status', 'paid')->sum('total_amount'));

        $yesterdayChangePercent = $yesterdayColl > 0 
            ? round((($todayTotalPaid - $yesterdayColl) / $yesterdayColl) * 100, 1) 
            : 0;
        
        // Collection Success Rate (approved vs total)
        $totalCollectionsCount = AgentCollection::count();
        $approvedCollectionsCount = AgentCollection::where('status', 'approved')->count();
        $collectionSuccessRate = $totalCollectionsCount > 0 
            ? round(($approvedCollectionsCount / $totalCollectionsCount) * 100, 1) 
            : 0;
        
        // Monthly Target Progress
        $monthlyCollected = (float) (AgentCollection::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'approved')
            ->sum('amount_collected')
            + Installment::whereMonth('paid_date', Carbon::now()->month)
            ->whereYear('paid_date', Carbon::now()->year)
            ->where('status', 'paid')
            ->sum('amount'));

        $lastMonthCollected = (float) (AgentCollection::whereMonth('created_at', Carbon::now()->subMonth()->month)
            ->whereYear('created_at', Carbon::now()->subMonth()->year)
            ->where('status', 'approved')
            ->sum('amount_collected')
            + Installment::whereMonth('paid_date', Carbon::now()->subMonth()->month)
            ->whereYear('paid_date', Carbon::now()->subMonth()->year)
            ->where('status', 'paid')
            ->sum('amount'));

        $monthlyTarget = $lastMonthCollected > 0 ? (float)($lastMonthCollected * 1.1) : max($monthlyCollected, 50000);
        $monthlyTargetProgress = $monthlyTarget > 0 ? min(round(($monthlyCollected / $monthlyTarget) * 100), 100) : 0;
        
        // Collection Methods (digital vs cash)
        $totalMethodCount = AgentCollection::count();
        $cashCount = AgentCollection::where('details', 'like', '%cash%')->count();
        $digitalCount = $totalMethodCount - $cashCount;
        $digitalPercent = $totalMethodCount > 0 ? round(($digitalCount / $totalMethodCount) * 100) : 50;
        $cashPercent = 100 - $digitalPercent;
        
        // Weekly trends from real data (last 7 days)
        $weeklyTrends = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayTotal = (float) (AgentCollection::whereDate('created_at', $date)->where('status', 'approved')->sum('amount_collected')
                + Installment::whereDate('paid_date', $date)->where('status', 'paid')->sum('amount')
                + LoanInstallment::whereDate('paid_date', $date)->where('status', 'paid')->sum('total_amount'));

            $weeklyTrends->push([
                'day' => $date->format('D'),
                'date' => $date->format('d M'),
                'total' => round($dayTotal / 100000, 2)
            ]);
        }

        // Dynamic Member Distribution percentages
        $activePct = $totalMembers > 0 ? round(($activeMembers / $totalMembers) * 100) : 0;
        $pendingPct = $totalMembers > 0 ? round(($pendingMembers / $totalMembers) * 100) : 0;
        $inactivePct = max(0, 100 - $activePct - $pendingPct);

        // Dynamic Recent Activity items
        $recentActivity = [];
        $latestUser = User::role('member')->latest('created_at')->first();
        if ($latestUser) {
            $recentActivity[] = [
                'icon' => 'fa-solid fa-user-plus',
                'bg' => 'var(--primary-light)',
                'color' => 'var(--primary)',
                'title' => "Member {$latestUser->name} registered (#MEM-{$latestUser->id})",
                'time' => $latestUser->created_at ? $latestUser->created_at->diffForHumans() : 'Recently'
            ];
        }

        $latestPaidInst = Installment::with(['user', 'committee'])->where('status', 'paid')->latest('updated_at')->first();
        if ($latestPaidInst) {
            $recentActivity[] = [
                'icon' => 'fa-regular fa-circle-check',
                'bg' => 'var(--success-bg)',
                'color' => 'var(--success)',
                'title' => "Installment #{$latestPaidInst->id} collected (" . ($latestPaidInst->user->name ?? 'Member') . ")",
                'time' => $latestPaidInst->updated_at ? $latestPaidInst->updated_at->diffForHumans() : 'Recently'
            ];
        }

        $latestLoan = Loan::with('user')->latest('created_at')->first();
        if ($latestLoan) {
            $recentActivity[] = [
                'icon' => 'fa-solid fa-hand-holding-dollar',
                'bg' => 'rgba(14, 165, 233, 0.15)',
                'color' => '#0ea5e9',
                'title' => "Loan #{$latestLoan->id} created for " . ($latestLoan->user->name ?? 'Borrower'),
                'time' => $latestLoan->created_at ? $latestLoan->created_at->diffForHumans() : 'Recently'
            ];
        }

        $latestLottery = Lottery::with('committee')->latest('draw_date')->first();
        if ($latestLottery) {
            $recentActivity[] = [
                'icon' => 'fa-solid fa-receipt',
                'bg' => '#f3e8ff',
                'color' => '#7c3aed',
                'title' => "Lottery draw processed for " . ($latestLottery->committee->name ?? 'Plan'),
                'time' => $latestLottery->draw_date ? Carbon::parse($latestLottery->draw_date)->diffForHumans() : 'Recently'
            ];
        }

        // Dynamic Priority Tasks
        $overdueLoansCount = LoanInstallment::where('status', 'pending')->whereDate('due_date', '<', $today)->count();
        $overdueInstCount = Installment::where('status', 'pending')->whereDate('due_date', '<', $today)->count();
        $totalPriorityTasks = $pendingMembers + $overdueLoansCount + $overdueInstCount;

        // Modules & Controls Count
        $modulesCount = 8; // Members, Agents, Committees, Loans, Installments, Materials, Lottery, KYC
        $controlsCount = Committee::count() + Loan::count() + Lottery::count() + Material::count();

        return [
            'modules_count' => $modulesCount,
            'controls_count' => $controlsCount,
            'total_members' => $totalMembers,
            'paid_members_count' => User::role('member')->whereHas('installments')->whereDoesntHave('installments', function ($query) use ($today) {
                $query->where('status', 'pending')->where('due_date', '<=', $today);
            })->count(),
            'today_collection' => $todayTotalPaid,
            'today_collection_formatted' => $this->formatAmountToCrOrLakh($todayTotalPaid),
            'yesterday_change_percent' => $yesterdayChangePercent,
            'monthly_target_progress' => (int) $monthlyTargetProgress,
            'monthly_collected' => $monthlyCollected,
            'monthly_target' => $monthlyTarget,
            'total_outstanding' => (float) Installment::where('status', 'pending')->sum('amount'),
            'total_due_amount' => (float) Installment::where('status', 'pending')->where('due_date', '<=', $today)->sum('amount'),
            
            // Figma stats
            'total_disbursements_formatted' => $totalDisbursedFormatted,
            'active_members_count' => $activeMembers,
            'active_agents_count' => $totalAgents,
            'total_collections_formatted' => $totalCollectionsFormatted,
            'kyc_compliance_rate' => $kycRate,
            'collection_success_rate' => $collectionSuccessRate,
            'total_collections_count' => $totalCollectionsCount,
            'approved_collections_count' => $approvedCollectionsCount,
            'pending_collections_count' => AgentCollection::where('status', 'pending')->count(),
            
            // Dynamic Lists & Charts
            'recent_transactions' => $recentTx,
            'monthly_trends' => $monthlyTrends,
            'weekly_trends' => $weeklyTrends,
            'member_distribution' => [
                'active' => $activePct,
                'pending' => $pendingPct,
                'inactive' => $inactivePct,
                'total_count' => $totalMembers,
                'active_count' => $activeMembers,
                'pending_count' => $pendingMembers,
                'inactive_count' => $inactiveMembers
            ],
            'collection_methods' => [
                'digital' => $digitalPercent,
                'cash' => $cashPercent
            ],
            'recent_activity' => $recentActivity,
            'priority_tasks' => [
                'total' => $totalPriorityTasks,
                'pending_kyc' => $pendingMembers,
                'overdue_accounts' => $overdueLoansCount + $overdueInstCount
            ]
        ];
        });
    }

    private function formatAmountToCrOrLakh($amount)
    {
        $amount = (float) $amount;
        if ($amount <= 0) {
            return '0';
        }
        if ($amount >= 10000000) {
            return round($amount / 10000000, 2) . 'Cr';
        } elseif ($amount >= 100000) {
            return round($amount / 100000, 2) . 'L';
        }
        return number_format($amount);
    }

    // 📈 Monthly Profit
    public function monthlyProfit()
    {
        return Installment::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->groupBy('month')
            ->get();
    }

    // 📅 Daily Collection
    public function dailyCollection()
    {
        return Installment::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
    }

    // 🎯 Lottery Summary
    public function lotteryStats()
    {
        return [
            'total_draws' => Lottery::count(),
            'today_draws' => Lottery::whereDate('created_at', Carbon::today())->count(),
        ];
    }
}