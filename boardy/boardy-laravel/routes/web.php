<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;



Route::get('/health', fn () => response()->json(['ok' => true]));

Route::get('/oauth/redirect', function () {

    $query = http_build_query([
        'client_id' => env('PASSPORT_CLIENT_ID'),
        'redirect_uri' => url('/oauth/callback'),
        'response_type' => 'code',
        'scope' => '',
    ]);

    return redirect('/oauth/authorize?' . $query);
});

Route::get('/oauth/callback', function (Request $request) {

    if (!$request->has('code')) {
        return 'Authorization code missing';
    }

    $response = Http::asForm()->post(
        url('/oauth/token'),
        [
            'grant_type' => 'authorization_code',
            'client_id' => env('PASSPORT_CLIENT_ID'),
            'client_secret' => env('PASSPORT_CLIENT_SECRET'),
            'redirect_uri' => url('/oauth/callback'),
            'code' => $request->code,
        ]
    );

    if ($response->failed()) {
        return $response->body();
    }

    $data = $response->json();

    session([
        'access_token' => $data['access_token'],
        'refresh_token' => $data['refresh_token'],
    ]);

    return redirect('/');
});

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
Route::view('/oauth/callback', 'oauth-callback');
