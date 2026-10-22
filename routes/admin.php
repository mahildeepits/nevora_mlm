<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SlotManagerController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\TransactionReportController;
use App\Http\Controllers\Admin\SettingController;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::controller(AdminAuthController::class)->group(function () {
        Route::get('login', 'loginForm')->name('login.form');
        Route::post('login', 'login')->name('login');
        Route::get('logout', 'logout')->name('logout');
    });

    Route::middleware('admin_auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

        // Dynamic Slot Manager
        Route::controller(SlotManagerController::class)->prefix('slots')->name('slots.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{slot}/edit', 'edit')->name('edit');
            Route::post('/{slot}/update', 'update')->name('update');
        });

        // User Management & Manual Overrides
        Route::controller(UserController::class)->prefix('users')->name('users.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{user}/edit', 'edit')->name('edit');
            Route::post('/{user}/update', 'update')->name('update');
            Route::get('/{user}/login', 'loginAsUser')->name('login_as');
            Route::post('/{user}/manual-activate', 'manualActivate')->name('manual_activate');
        });

        // Announcements Management
        Route::resource('announcements', AnnouncementController::class);

        // Unified Transaction Ledger & Reports
        Route::get('/transactions', [TransactionReportController::class, 'index'])->name('transactions.index');

        // System Settings & Software Data Reset
        Route::controller(SettingController::class)->prefix('settings')->name('settings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/update', 'update')->name('update');
            Route::post('/reset-database', 'resetDatabase')->name('reset_database');
        });
    });
});
