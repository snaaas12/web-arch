<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return redirect()->route('posts.index');
})->middleware(['auth'])->name('dashboard');

Route::resource('posts', PostController::class);
Route::post('/comments', [CommentController::class, 'store'])->name('comments.store')->middleware('auth');

require __DIR__.'/auth.php';
Route::get('/dashboard', fn() => redirect()->route('posts.index'))->middleware(['auth'])->name('dashboard');
Route::get('/dashboard', fn() => redirect()->route('posts.index'))->middleware(['auth'])->name('dashboard');

Route::get('/auth/github', [App\Http\Controllers\Auth\GitHubController::class, 'redirect'])->name('auth.github');
Route::get('/auth/github/callback', [App\Http\Controllers\Auth\GitHubController::class, 'callback']);
