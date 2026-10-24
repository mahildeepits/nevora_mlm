<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Member\DashboardController;
use App\Http\Controllers\Member\ProfileController;
use App\Http\Controllers\Member\NetworkController;
use App\Http\Controllers\Member\WalletController;
use App\Http\Controllers\Member\AnnouncementController;

Route::prefix('member')->name('member.')->middleware(['member_auth', 'block_user_actions'])->group(function () {
    // Dedicated Under Process / Action Blocked Notice Page
    Route::get('/action-blocked', function () {
        return view('member.action-blocked', [
            'message' => 'This action is under process, It will be available soon.'
        ]);
    })->name('action_blocked');

    // Dashboard & Payment Tracking
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/payment-route-info', [DashboardController::class, 'paymentRouteInfo'])->name('payment.route_info');
    Route::post('/track-payment', [DashboardController::class, 'trackPayment'])->name('track.payment');

    // Dedicated Invite & Direct Downline Onboarding
    Route::get('/invite', [NetworkController::class, 'invite'])->name('invite');
    Route::post('/invite', [NetworkController::class, 'registerDownline'])->name('invite.register');

    // Profile & Account Management
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/update', 'updateProfile')->name('update');
        Route::post('/avatar', 'updateAvatar')->name('avatar');
        Route::post('/password', 'updatePassword')->name('password');
        Route::post('/wallet-otp', 'sendWalletOtp')->name('wallet.otp');
        Route::post('/wallet-address', 'updateWalletAddress')->name('wallet.address');
        Route::post('/bind-web3-wallet', 'bindWeb3Wallet')->name('wallet.bind_web3');
    });

    // 4 Network Modules
    Route::controller(NetworkController::class)->prefix('network')->name('network.')->group(function () {
        Route::get('/tree/{memberId?}', 'tree')->name('tree');
        Route::get('/directs', 'myDirects')->name('directs');
        Route::get('/hierarchy', 'teamHierarchy')->name('hierarchy');
        Route::get('/business', 'downlineBusiness')->name('business');
    });

    // Financials: Unified Transactions Ledger
    Route::controller(WalletController::class)->prefix('wallet')->name('wallet.')->group(function () {
        Route::get('/transactions', 'transactions')->name('transactions');
    });

    // Support: Announcements & News
    Route::controller(AnnouncementController::class)->prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', 'index')->name('index');
    });
});
