<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\ArticleController as PublicArticleController;
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
        Route::post('/pages/{page}/revisions/{revision}/restore', [PageBuilderController::class, 'restore'])->name('pages.revisions.restore');

        Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles.index');
        Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
        Route::post('/articles', [AdminArticleController::class, 'store'])->name('articles.store');
        Route::get('/articles/{post}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
        Route::put('/articles/{post}', [AdminArticleController::class, 'update'])->name('articles.update');
        Route::delete('/articles/{post}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::get('/media/library', [MediaController::class, 'library'])->name('media.library');
        Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    });

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/blog', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/blog/categoria/{category:slug}', [PublicArticleController::class, 'byCategory'])->name('articles.category');
Route::get('/blog/{post:slug}', [PublicArticleController::class, 'show'])->name('articles.show');

Route::get('/', [PageController::class, 'home'])->name('pages.home');
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('pages.show');
