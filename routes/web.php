<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\PreventLoginIfAuthenticated;
use App\Http\Controllers\AuthController;

Route::redirect('/', '/login');

// Login
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware(PreventLoginIfAuthenticated::class)
    ->name('login');
Route::post('/login', [AuthController::class, 'login']);


// Logout
Route::post('/logout', [AuthController::class, 'logout'])
     ->middleware('auth')
     ->name('logout');
   

// Dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.dashboard'); 
    })->name('dashboard');
    
});