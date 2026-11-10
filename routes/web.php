<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ExportClaimController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Attachment preview/download: owner or admin (checked in controller)
    Route::get('/claims/attachments/{attachment}', [ClaimController::class, 'attachment'])->name('claims.attachment');

    // ---- Staff portal ----
    Route::middleware(EnsureRole::class.':staff')->group(function () {
        Route::get('/dashboard', [ClaimController::class, 'dashboard'])->name('dashboard');
        Route::get('/claims/create', [ClaimController::class, 'create'])->name('claims.create');
        Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
        Route::get('/claims/history', [ClaimController::class, 'history'])->name('claims.history');
    });

    // ---- Admin portal ----
    Route::middleware(EnsureRole::class.':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::patch('/claims/{claim}/status', [AdminController::class, 'updateStatus'])->name('claims.status');
        Route::get('/claims/export', [ExportClaimController::class, 'export'])->name('claims.export');
    });
});
