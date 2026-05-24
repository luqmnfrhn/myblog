<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\PostCurationController;
use App\Http\Controllers\Auth\SocialController;
use App\Http\Controllers\CircleMessageController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\ReadingCircleController;
use App\Http\Controllers\TipController;
use App\Http\Controllers\WriterPostController;
use App\Http\Controllers\WriterProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PostController::class, 'index'])->name('posts.index');
Route::get('/writers/{writer}', [WriterProfileController::class, 'show'])->name('writers.show');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::prefix('writer')->name('writer.')->group(function (): void {
        Route::get('posts', [WriterPostController::class, 'index'])->name('posts.index');
        Route::get('posts/create', [WriterPostController::class, 'create'])->name('posts.create');
        Route::post('posts', [WriterPostController::class, 'store'])->name('posts.store');
        Route::get('posts/{post}/edit', [WriterPostController::class, 'edit'])->name('posts.edit');
        Route::patch('posts/{post}', [WriterPostController::class, 'update'])->name('posts.update');
        Route::patch('posts/{post}/publish', [WriterPostController::class, 'publish'])->name('posts.publish');
        Route::delete('posts/{post}', [WriterPostController::class, 'destroy'])->name('posts.destroy');
    });

    Route::post('/writers/{writer}/follow', [FollowController::class, 'follow'])->name('writers.follow');
    Route::delete('/writers/{writer}/unfollow', [FollowController::class, 'unfollow'])->name('writers.unfollow');
    Route::get('/writers/{writer}/tip', [TipController::class, 'create'])->name('writers.tip');
    Route::post('/writers/{writer}/tip', [TipController::class, 'store'])->name('writers.tip.store');

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::post('/posts/{post}/reactions', [ReactionController::class, 'store'])->name('posts.reactions.store');
    Route::post('/posts/{post}/circles', [ReadingCircleController::class, 'store'])->name('posts.circles.store');
    Route::get('/circles/{circle}', [ReadingCircleController::class, 'show'])->name('circles.show');
    Route::post('/circles/{circle}/join', [ReadingCircleController::class, 'join'])->name('circles.join');
    Route::post('/circles/{circle}/messages', [CircleMessageController::class, 'store'])->name('circles.messages.store');
});

// Social auth
Route::get('/auth/{provider}', [SocialController::class, 'redirect'])
    ->name('auth.social.redirect')
    ->where('provider', 'google|github');

Route::get('/auth/{provider}/callback', [SocialController::class, 'callback'])
    ->name('auth.social.callback')
    ->where('provider', 'google|github');

// Admin auth
Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('login', [AdminLoginController::class, 'store']);
    });

    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    Route::middleware('admin')->group(function () {
        Route::get('dashboard', fn () => view('admin.dashboard'))->name('dashboard');
        Route::get('posts', [AdminPostController::class, 'index'])->name('posts.index');
        Route::delete('posts/{post}', [AdminPostController::class, 'destroy'])->name('posts.destroy');
        Route::patch('posts/{post}/feature', [PostCurationController::class, 'feature'])->name('posts.feature');
    });
});

require __DIR__.'/auth.php';
