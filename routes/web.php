<?php

use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function (): void {
        Route::redirect('/', '/admin/pages');

        Route::get('/pages', [PageBuilderController::class, 'index'])->name('pages.index');
        Route::get('/pages/create', [PageBuilderController::class, 'create'])->name('pages.create');
        Route::post('/pages', [PageBuilderController::class, 'store'])->name('pages.store');
        Route::get('/pages/{page}/edit', [PageBuilderController::class, 'edit'])->name('pages.edit');
        Route::put('/pages/{page}', [PageBuilderController::class, 'update'])->name('pages.update');

        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/', [PageController::class, 'home'])->name('pages.home');
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('pages.show');
