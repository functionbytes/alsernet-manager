<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\ForgotPasswordController;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\RegisterController;
use Modules\Auth\Http\Controllers\ResetPasswordController;
use Modules\Auth\Http\Controllers\ValidationController;
use Modules\Auth\Http\Controllers\VerificationController;

/*
|--------------------------------------------------------------------------
| Auth Public Routes
|--------------------------------------------------------------------------
|
| Rutas públicas de autenticación (login, register, password reset)
| Prefix: /auth (aplicado por ServiceProvider)
| Name: auth.* (aplicado por ServiceProvider)
| Middleware: web, guest
|
*/

// Login routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Logout route (requires auth middleware override)
Route::get('/logout', [LoginController::class, 'logout'])->name('logout')->withoutMiddleware('guest')->middleware('auth');

// Password reset routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequest'])->name('password.request');
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');

// Email verification routes
Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])->name('verification.verify');
Route::get('/email/resend', [VerificationController::class, 'resend'])->name('verification.resend');

// Validation route
Route::get('/validation', [ValidationController::class, 'show'])->name('validation');

// Register routes (uncomment when ready to enable registration)
// Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
// Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
