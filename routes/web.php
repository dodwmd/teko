<?php

use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// Redirect base URL to the Orchid admin login
Route::get('/', function () {
    return redirect('/admin/login');
});

// Add a proper login route alias for redirects
Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

// Socialite Authentication Routes
Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
    ->name('socialite.redirect')
    ->where('provider', 'github|google');

Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
    ->name('socialite.callback')
    ->where('provider', 'github|google');

// Dashboard routes protected by authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect('/admin/main');
    })->name('dashboard');

    // Comment system routes
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::post('/', [CommentController::class, 'store'])->name('store');
        Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
        Route::get('/list', [CommentController::class, 'getComments'])->name('list');
    });
});
