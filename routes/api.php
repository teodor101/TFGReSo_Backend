<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{post}/comments', [CommentController::class, 'index'])->whereNumber('post');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::delete('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);
    Route::post('/createpost', [PostController::class, 'store']);
    Route::get('/posts/feed', [PostController::class, 'feed']);
    Route::post('/posts/{post}/like', [LikeController::class, 'togglePostLike'])->whereNumber('post');
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->whereNumber('post');
    Route::post('/comments/{comment}/like', [LikeController::class, 'toggleCommentLike'])->whereNumber('comment');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->whereNumber('comment');
    Route::put('/posts/{post}', [PostController::class, 'update'])->whereNumber('post');
    Route::get('/getPosts', [AuthController::class, 'getCurrentUserWithPosts']);
    Route::delete('/deletePosts/{id}', [PostController::class, 'destroy']);
});