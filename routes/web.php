<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\PreventLoginIfAuthenticated;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;

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
   

Route::middleware('auth')->group(function () {
    // Rute Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/tambah_produk', [DashboardController::class, 'create'])->name('tambah_produk');
    Route::post('/dashboard/store_produk', [DashboardController::class, 'store'])->name('store_produk');
    Route::get('/dashboard/edit_produk/{id}', [DashboardController::class, 'edit'])->name('edit_produk');
    Route::put('/dashboard/update_produk/{id}', [DashboardController::class, 'update'])->name('update_produk');
    Route::delete('/dashboard/delete_produk/{id}', [DashboardController::class, 'destroy'])->name('delete_produk');
    Route::get('/export-products', [DashboardController::class, 'export'])->name('export_products');

    // Rute Kategori
    Route::resource('categories', CategoryController::class);
});
