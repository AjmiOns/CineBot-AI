<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CinebotController;
use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Page d'accueil
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect()->route('chatbot') : redirect()->route('login');
})->name('home');

/*
|--------------------------------------------------------------------------
| Authentification (invités uniquement)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Application (utilisateurs connectés uniquement)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/chatbot', function () {
        return view('chatbot');
    })->name('chatbot');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::delete('/profile', [AuthController::class, 'destroyAccount'])->name('account.destroy');
});

/*
|--------------------------------------------------------------------------
| Dashboard admin (réservé aux administrateurs)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/admin/export', [AdminDashboardController::class, 'exportCsv'])->name('admin.export');
});
