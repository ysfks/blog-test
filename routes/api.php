<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthenticationController, PostController};
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthenticationController::class, 'register']);
Route::post('login', [AuthenticationController::class, 'login']);
Route::middleware('auth:api')->group(function() {
    Route::post('logout', [AuthenticationController::class, 'logout']);

    /**
     * POSTS
     */
    Route::get('/posts', [PostController::class, 'index']);
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{post}', [PostController::class, 'show']);
    Route::put('/posts/{post}', [PostController::class, 'update'])->middleware('scope:update-post');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->middleware('scope:delete-post');
});
