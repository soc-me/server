<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});


// Post Routes
Route::prefix('post')->group(function(){
    Route::controller(PostController::class)->group(function(){
        Route::get('/all', 'index')->name('post_all');
        Route::get('/{id}', 'show')->name('post_show');
        Route::post('/create', 'store')->middleware('auth:sanctum')->name('post_create');
        Route::put('/update/{id}', 'update')->middleware('auth:sanctum')->name('post_update');
        Route::delete('/delete/{id}', 'destroy')->middleware('auth:sanctum')->name('post_delete');
    });
});

// Like Routes 
Route::prefix('like')->controller(LikeController::class)->group(function(){
    Route::get('/all', 'index')->name('like_all');
    Route::get('/{id}', 'show')->name('like_show');
    Route::post('/create', 'store')->middleware('auth:sanctum')->name('like_create');
    Route::put('/update/{id}', 'update')->middleware('auth:sanctum')->name('like_update');
    Route::delete('/delete/{userID}', 'destroy')->middleware('auth:sanctum')->name('like_delete');
    Route::delete('/likesonpost/{postID}', 'likesOnPost')->name('likes_on_post');
});