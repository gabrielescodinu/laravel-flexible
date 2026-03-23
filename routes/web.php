<?php

use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\MenuController as AdminMenuController;
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\ArticleController as PublicArticleController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Support\Access\Permissions;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->name('admin.')
    ->middleware('auth')
    ->group(function (): void {
        Route::get('/', function () {
            $user = request()->user();

            if ($user?->hasPermission(Permissions::MANAGE_PAGES)) {
                return redirect()->route('admin.pages.index');
            }

            if ($user?->hasPermission(Permissions::MANAGE_ARTICLES)) {
                return redirect()->route('admin.articles.index');
            }

            if ($user?->hasPermission(Permissions::MANAGE_CATEGORIES)) {
                return redirect()->route('admin.categories.index');
            }

            if ($user?->hasPermission(Permissions::MANAGE_MEDIA)) {
                return redirect()->route('admin.media.index');
            }

            if ($user?->hasPermission(Permissions::MANAGE_MENUS)) {
                return redirect()->route('admin.menus.edit');
            }

            if ($user?->hasPermission(Permissions::MANAGE_USERS)) {
                return redirect()->route('admin.users.index');
            }

            abort(403, 'Nessuna sezione disponibile per il tuo account.');
        })->name('dashboard');

        Route::middleware('permission:'.Permissions::MANAGE_PAGES)->group(function (): void {
            Route::get('/pages', [PageBuilderController::class, 'index'])->name('pages.index');
            Route::get('/pages/trash', [PageBuilderController::class, 'trash'])->name('pages.trash');
            Route::get('/pages/create', [PageBuilderController::class, 'create'])->name('pages.create');
            Route::post('/pages', [PageBuilderController::class, 'store'])->name('pages.store');
            Route::get('/pages/{page}/edit', [PageBuilderController::class, 'edit'])->name('pages.edit');
            Route::put('/pages/{page}', [PageBuilderController::class, 'update'])->name('pages.update');
            Route::delete('/pages/{page}', [PageBuilderController::class, 'destroy'])->name('pages.destroy');
            Route::post('/pages/trash/{pageId}/restore', [PageBuilderController::class, 'restoreFromTrash'])->name('pages.restore');
            Route::delete('/pages/trash/{pageId}/force', [PageBuilderController::class, 'forceDeleteFromTrash'])->name('pages.force-delete');
            Route::post('/pages/{page}/revisions/{revision}/restore', [PageBuilderController::class, 'restore'])->name('pages.revisions.restore');
        });

        Route::middleware('permission:'.Permissions::MANAGE_ARTICLES)->group(function (): void {
            Route::get('/articles', [AdminArticleController::class, 'index'])->name('articles.index');
            Route::get('/articles/trash', [AdminArticleController::class, 'trash'])->name('articles.trash');
            Route::get('/articles/create', [AdminArticleController::class, 'create'])->name('articles.create');
            Route::post('/articles', [AdminArticleController::class, 'store'])->name('articles.store');
            Route::get('/articles/{post}/edit', [AdminArticleController::class, 'edit'])->name('articles.edit');
            Route::put('/articles/{post}', [AdminArticleController::class, 'update'])->name('articles.update');
            Route::delete('/articles/{post}', [AdminArticleController::class, 'destroy'])->name('articles.destroy');
            Route::post('/articles/trash/{postId}/restore', [AdminArticleController::class, 'restoreFromTrash'])->name('articles.restore');
            Route::delete('/articles/trash/{postId}/force', [AdminArticleController::class, 'forceDeleteFromTrash'])->name('articles.force-delete');
        });

        Route::middleware('permission:'.Permissions::MANAGE_CATEGORIES)->group(function (): void {
            Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
            Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
            Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
        });

        Route::middleware('permission:'.Permissions::MANAGE_MENUS)->group(function (): void {
            Route::get('/menus', [AdminMenuController::class, 'edit'])->name('menus.edit');
            Route::put('/menus', [AdminMenuController::class, 'update'])->name('menus.update');
        });

        Route::middleware('permission:'.Permissions::MANAGE_MEDIA)->group(function (): void {
            Route::get('/media', [MediaController::class, 'index'])->name('media.index');
            Route::post('/media', [MediaController::class, 'store'])->name('media.store');
            Route::get('/media/library', [MediaController::class, 'library'])->name('media.library');
            Route::post('/media/upload', [MediaController::class, 'upload'])->name('media.upload');
            Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
        });

        Route::middleware('permission:'.Permissions::MANAGE_USERS)->group(function (): void {
            Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
            Route::get('/users/create', [UserManagementController::class, 'create'])->name('users.create');
            Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
            Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        });
    });

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::get('/blog', [PublicArticleController::class, 'index'])->name('articles.index');
Route::get('/blog/categoria/{category:slug}', [PublicArticleController::class, 'byCategory'])->name('articles.category');
Route::get('/blog/{category:slug}/{post:slug}', [PublicArticleController::class, 'showByCategory'])->name('articles.show.category');
Route::get('/blog/{post:slug}', [PublicArticleController::class, 'show'])->name('articles.show');

Route::get('/', [PageController::class, 'home'])->name('pages.home');
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+')
    ->name('pages.show');
