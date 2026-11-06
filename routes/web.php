<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    // return redirect()->route('member.login.form');
    return view('auth.login');
});

// Direct Public Presentation Deck Link
Route::get('/presentation', function () {
    return response()->file(public_path('docs/NEVORA_Business_Plan_Presentation.pdf'));
})->name('public.presentation');

// WalletConnect / Reown Domain Verification Endpoint
Route::get('/.well-known/walletconnect.txt', function () {
    $path = public_path('.well-known/walletconnect.txt');
    if (file_exists($path)) {
        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/plain',
            'Cache-Control' => 'no-cache, private'
        ]);
    }
    return response('File not found', 404);
});

// Member Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('login', 'loginForm')->name('member.login.form');
    Route::post('login', 'login')->name('member.login');
    Route::get('register', 'registerForm')->name('member.register.form');
    Route::post('register', 'register')->name('member.register');
    Route::post('register-payee-preview', 'getRegistrationPayee')->name('member.register.payee_preview');
    Route::post('stash-registration', 'stashRegistrationState')->name('member.register.stash');
    Route::get('check-sponsor', 'checkSponsor')->name('member.check_sponsor');
    Route::get('logout', 'logout')->name('member.logout');
});

// Member Routes Include
require __DIR__ . '/member.php';

// Admin Routes Include
require __DIR__ . '/admin.php';
