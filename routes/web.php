<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('member.login.form');
});

// Direct Public Presentation Deck Link
Route::get('/presentation', function () {
    return response()->file(public_path('docs/NEVORA_Business_Plan_Presentation.pdf'));
})->name('public.presentation');

// Member Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('login', 'loginForm')->name('member.login.form');
    Route::post('login', 'login')->name('member.login');
    Route::post('login-web3', 'loginWeb3')->name('member.login.web3');
    Route::get('register', 'registerForm')->name('member.register.form');
    Route::post('register', 'register')->name('member.register');
    Route::get('check-sponsor', 'checkSponsor')->name('member.check_sponsor');
    Route::get('logout', 'logout')->name('member.logout');
});

// Member Routes Include
require __DIR__ . '/member.php';

// Admin Routes Include
require __DIR__ . '/admin.php';
