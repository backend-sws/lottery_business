<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\CommitteeController;
use App\Http\Controllers\Admin\InstallmentController;
use App\Http\Controllers\Admin\LotteryController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\NotificationController;

Route::prefix('admin')->group(function () {

    // 🔐 Admin Login (No Auth Required)
    Route::post('/login', [AuthController::class, 'login']);
});

// 📱 Public User Routes

Route::post('/login', [\App\Http\Controllers\Api\UserAuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\UserAuthController::class, 'register']);

// 🛡️ Protected Routes
Route::middleware(['auth:sanctum'])->group(function () {

    // 🏢 Member App APIs
    Route::middleware('role:member|agent')->prefix('member')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Api\MemberDashboardController::class, 'index']);
        Route::get('/profile', [\App\Http\Controllers\Api\MemberDashboardController::class, 'profile']);
        Route::get('/documents', [\App\Http\Controllers\Api\MemberDashboardController::class, 'documents']);
        Route::post('/documents/upload', [\App\Http\Controllers\Api\MemberDashboardController::class, 'uploadDocument']);
        Route::post('/bank-account', [\App\Http\Controllers\Api\MemberDashboardController::class, 'updateBankAccount']);
        Route::get('/lotteries', [\App\Http\Controllers\Api\MemberDashboardController::class, 'lotteries']);
        Route::get('/loans/{id}', [\App\Http\Controllers\Api\MemberDashboardController::class, 'showLoan']);
        Route::get('/installments', [\App\Http\Controllers\Api\MemberDashboardController::class, 'installments']);
        Route::get('/loans', [\App\Http\Controllers\Api\MemberDashboardController::class, 'loans']);
        Route::get('/committees', [\App\Http\Controllers\Api\MemberDashboardController::class, 'committees']);
        Route::get('/passbook', [\App\Http\Controllers\Api\MemberDashboardController::class, 'passbook']);
    });

    // 🕵️‍♂️ Agent App APIs
    Route::middleware('role:agent')->prefix('agent')->group(function () {
        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Agent\DashboardController::class, 'dashboard']);
        
        // Profile & Settings
        Route::get('/profile', [\App\Http\Controllers\Agent\ProfileController::class, 'index']);
        Route::post('/profile/update', [\App\Http\Controllers\Agent\ProfileController::class, 'update']);
        Route::get('/qr-code', [\App\Http\Controllers\Agent\ProfileController::class, 'qrCode']);
        
        // Members / Clients
        Route::get('/search-member', [\App\Http\Controllers\Agent\MemberController::class, 'search']);
        Route::get('/clients', [\App\Http\Controllers\Agent\ClientController::class, 'index']);
        Route::post('/clients', [\App\Http\Controllers\Agent\ClientController::class, 'store']);
        Route::get('/clients/{id}', [\App\Http\Controllers\Agent\ClientController::class, 'show']);
        Route::post('/clients/kyc', [\App\Http\Controllers\Agent\ClientController::class, 'submitKyc']);
        
        // Collections
        Route::post('/collections/submit', [\App\Http\Controllers\Agent\CollectionController::class, 'submit']);
        Route::get('/collections', [\App\Http\Controllers\Agent\CollectionController::class, 'history']);

        // Support
        Route::post('/support/ticket', [\App\Http\Controllers\Agent\SupportController::class, 'submitTicket']);
    });

    // 🔒 Secure Document Viewer
    Route::get('/documents/kyc/{userId}/{filename}', [\App\Http\Controllers\Api\DocumentController::class, 'showKyc']);

});

// 🖼️ Public Profile Photo Viewer
Route::get('/profile-photo/{userId}/{filename}', [\App\Http\Controllers\Api\DocumentController::class, 'showProfilePhoto']);

// 🧑 User Specific API Routes
Route::prefix('user')->group(function () {
    Route::post('/login', [\App\Http\Controllers\Api\UserAuthController::class, 'login']);
Route::post('/register', [\App\Http\Controllers\Api\UserAuthController::class, 'register']);

    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\User\DashboardController::class, 'index']);
        Route::post('/logout', [\App\Http\Controllers\Api\UserAuthController::class, 'logout']);

        // 👤 Profile & Vault
        Route::get('/profile', [\App\Http\Controllers\User\ProfileController::class, 'index']);
        Route::post('/profile/update', [\App\Http\Controllers\User\ProfileController::class, 'update']);
        Route::get('/vault', [\App\Http\Controllers\User\ProfileController::class, 'vault']);
        Route::post('/vault/upload', [\App\Http\Controllers\User\ProfileController::class, 'uploadVault']);
        Route::get('/terms-conditions', [\App\Http\Controllers\User\TermsConditionController::class, 'index']);
        Route::get('/payment-setting', [\App\Http\Controllers\User\PaymentSettingController::class, 'show']);
        Route::get('/notifications', [\App\Http\Controllers\Api\MemberDashboardController::class, 'notifications']);

        // 🏢 Committees
        Route::get('/committees', [\App\Http\Controllers\User\CommitteeController::class, 'index']);
        Route::get('/committees/{id}', [\App\Http\Controllers\User\CommitteeController::class, 'show']);
        Route::post('/committees/{id}/join', [\App\Http\Controllers\User\CommitteeController::class, 'join']);
        Route::get('/my-committees', [\App\Http\Controllers\User\CommitteeController::class, 'myCommittees']);

        // 🏗️ Materials & Stocks
        Route::get('/materials', [\App\Http\Controllers\User\MaterialController::class, 'index']);
        Route::get('/materials/stocks', [\App\Http\Controllers\User\MaterialController::class, 'stocks']);

        // 💳 Loans
        Route::get('/loans', [\App\Http\Controllers\User\LoanController::class, 'index']);
        Route::get('/loans/{id}', [\App\Http\Controllers\User\LoanController::class, 'show']);

        // 💰 Installments
        Route::get('/installments/pending', [\App\Http\Controllers\User\InstallmentController::class, 'pending']);
        Route::get('/installments/paid', [\App\Http\Controllers\User\InstallmentController::class, 'paid']);

        // 🏆 Lottery
        Route::get('/lotteries/winners', [\App\Http\Controllers\User\LotteryController::class, 'winners']);
        Route::get('/lotteries/history', [\App\Http\Controllers\User\LotteryController::class, 'history']);
        Route::get('/lotteries/setting', [\App\Http\Controllers\User\LotteryController::class, 'showSettings']);

        // 💸 Payments
        Route::post('/payments/pay', [\App\Http\Controllers\User\PaymentController::class, 'pay']);
    });
});

Route::prefix('admin')->group(function () {
    // 🔒 Protected Admin Routes
    Route::middleware(['auth:sanctum', 'role:Super Admin'])->group(function () {

        // 🚪 Logout
        Route::post('/logout', [AuthController::class, 'logout']);

        // 👤 Users
        Route::apiResource('users', UserController::class);

        // 📊 Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/dashboard/daily-collection', [DashboardController::class, 'dailyCollection']);
        Route::get('/dashboard/monthly-profit', [DashboardController::class, 'monthlyProfit']);
        Route::get('/dashboard/paid-members', [DashboardController::class, 'paidMembersList']);
        Route::get('/dashboard/due-members', [DashboardController::class, 'dueMembersList']);

        // 💰 Accounting
        Route::get('/accounting/member-ledger/{id}', [\App\Http\Controllers\Admin\AccountingController::class, 'memberLedger']);
        Route::get('/accounting/committee-ledger/{id}', [\App\Http\Controllers\Admin\AccountingController::class, 'committeeLedger']);
        Route::get('/accounting/pnl', [\App\Http\Controllers\Admin\AccountingController::class, 'profitAndLoss']);
        Route::get('/accounting/balance-sheet', [\App\Http\Controllers\Admin\AccountingController::class, 'balanceSheet']);

        // 👥 Members
        Route::post('/members/{id}/change-password', [MemberController::class, 'changePassword']);
        Route::post('/members/{user}/enroll', [MemberController::class, 'enroll']);
        Route::get('/members/{id}/impersonate', [MemberController::class, 'impersonate']);
        Route::apiResource('members', MemberController::class);

        // 🏦 Committees
        Route::get('/committees/{committee}/collection-stats', [CommitteeController::class, 'collectionStats']);
        Route::apiResource('committees', CommitteeController::class);
        Route::get('/terms-conditions', [\App\Http\Controllers\Admin\TermsConditionController::class, 'show']);
        Route::post('/terms-conditions', [\App\Http\Controllers\Admin\TermsConditionController::class, 'update']);
        Route::get('/payment-setting', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'show']);
        Route::post('/payment-setting', [\App\Http\Controllers\Admin\PaymentSettingController::class, 'update']);

        // 🏗️ Materials & Stocks Admin Management
        Route::apiResource('materials', \App\Http\Controllers\Admin\MaterialController::class);
        Route::apiResource('material-stocks', \App\Http\Controllers\Admin\MaterialStockController::class);

        // 💳 Loans
        Route::get('/loan-installments', [\App\Http\Controllers\Admin\LoanController::class, 'installments']);
        Route::delete('/loan-installments/{id}', [\App\Http\Controllers\Admin\LoanController::class, 'destroyInstallment']);
        Route::post('/loans/{id}/collect', [\App\Http\Controllers\Admin\LoanController::class, 'collect']);
        Route::apiResource('loans', \App\Http\Controllers\Admin\LoanController::class);

        // 💰 Installments
        Route::post('/installments/collect', [\App\Http\Controllers\Admin\InstallmentController::class, 'collect']);
        Route::post('/installments/{id}/collect', [\App\Http\Controllers\Admin\InstallmentController::class, 'collect']);
        Route::get('/installments/pending', [\App\Http\Controllers\Admin\InstallmentController::class, 'pending']);
        Route::post('/installments/send-warnings', [\App\Http\Controllers\Admin\InstallmentController::class, 'sendWarnings']);
        Route::post('/installments/send-payment-reminders', [\App\Http\Controllers\Admin\InstallmentController::class, 'sendPaymentReminders']);
        Route::apiResource('installments', \App\Http\Controllers\Admin\InstallmentController::class);

        // 🔔 Notifications (Admin)
        Route::post('/notifications/send-custom', [\App\Http\Controllers\Admin\NotificationController::class, 'sendCustom']);

        // 🎟️ Lotteries
        Route::post('/lotteries', [\App\Http\Controllers\Admin\LotteryController::class, 'store']);
        Route::post('/lotteries/{id}/draw', [\App\Http\Controllers\Admin\LotteryController::class, 'drawWinner']);
        Route::get('/lotteries/{id}/winners', [\App\Http\Controllers\Admin\LotteryController::class, 'getWinnersByLottery']);

        // 🕵️‍♂️ Agent Management (Admin)
        Route::post('/agents/targets', [\App\Http\Controllers\Admin\AgentController::class, 'assignTarget']);
        Route::get('/agents/targets', [\App\Http\Controllers\Admin\AgentController::class, 'getTargets']);
        Route::get('/agents/collections', [\App\Http\Controllers\Admin\AgentController::class, 'getCollections']);
        Route::post('/agents/collections/{id}/approve', [\App\Http\Controllers\Admin\AgentController::class, 'approveCollection']);
        Route::post('/agents/collections/{id}/reject', [\App\Http\Controllers\Admin\AgentController::class, 'rejectCollection']);
        Route::delete('/agents/collections/{id}', [\App\Http\Controllers\Admin\AgentController::class, 'deleteCollection']);

        // 💰 Payouts
        Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index']);
        Route::post('/payouts/{id}/pay', [\App\Http\Controllers\Admin\PayoutController::class, 'pay']);

        // 🎯 Lottery
        Route::post('/lotteries/draw/{committee_id}', [LotteryController::class, 'draw']);
        Route::post('/lotteries/manual-draw', [LotteryController::class, 'manualDraw']);
        Route::get('/lotteries/setting', [LotteryController::class, 'showSettings']);
        Route::post('/lotteries/setting', [LotteryController::class, 'updateSettings']);
        Route::apiResource('lotteries', LotteryController::class)->except(['store']);

        // 📈 Reports
        Route::get('/reports/collection', [ReportController::class, 'collectionReport']);
        Route::get('/reports/monthly', [ReportController::class, 'monthlyReport']);
        Route::get('/reports/committees', [ReportController::class, 'committeeReport']);
        Route::get('/reports/date-range/{start}/{end}', [ReportController::class, 'dateRangeReport']);

        // 📲 Notifications
        Route::post('/notifications/sms', [NotificationController::class, 'sendSMS']);
        Route::post('/notifications/whatsapp', [NotificationController::class, 'sendWhatsApp']);

        // 👮 KYC Verification
        Route::post('/kyc/submit', [\App\Http\Controllers\Admin\KycController::class, 'submit']);
        Route::post('/kyc/{id}/approve', [\App\Http\Controllers\Admin\KycController::class, 'approve']);
        Route::post('/kyc/{id}/reject', [\App\Http\Controllers\Admin\KycController::class, 'reject']);

    });
});