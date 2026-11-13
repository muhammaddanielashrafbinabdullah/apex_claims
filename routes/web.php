<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ExportClaimController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// Typing the site address always ends on the login page (any logged-in session is closed first).
Route::get('/', function () {
    if (auth()->check()) {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }

    return redirect()->route('login');
});

// No "guest" middleware here: a logged-in user who opens /login is sent to THEIR OWN dashboard
// (see AuthController@showLogin), so admins never land on a staff-only page.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Attachment preview/download: owner or admin (checked in controller)
    Route::get('/claims/attachments/{attachment}', [ClaimController::class, 'attachment'])->name('claims.attachment');

    // ---- Staff portal ----
    Route::middleware(EnsureRole::class.':staff')->group(function () {
        Route::get('/claims/create', [ClaimController::class, 'create'])->name('claims.create');
        Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
        Route::get('/claims/history', [ClaimController::class, 'history'])->name('claims.history');
    });

    // ---- Admin portal ----
    Route::middleware(EnsureRole::class.':admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::patch('/claims/{claim}/status', [AdminController::class, 'updateStatus'])->name('claims.status');
        Route::patch('/claims/{claim}/items/{item}/disallow', [AdminController::class, 'disallowItem'])->name('claims.items.disallow');
        Route::patch('/claims/{claim}/items/{item}/restore', [AdminController::class, 'restoreItem'])->name('claims.items.restore');
        Route::get('/claims/export', [ExportClaimController::class, 'export'])->name('claims.export');
    });
});
