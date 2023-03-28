<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
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
        Route::get('/following', 'followingPosts')->middleware('auth:sanctum')->name('following_posts');
        Route::get('/complete/{post_ID}', 'show')->name('post_show');
        Route::post('/create', 'store')->middleware('auth:sanctum')->name('post_create');
        Route::put('/update/{id}', 'update')->middleware('auth:sanctum')->name('post_update');
        Route::delete('/delete/{id}', 'destroy')->middleware('auth:sanctum')->name('post_delete');
        Route::get('/user/{id}', 'postsByUser')->name('posts_by_user');
        Route::get('/minimal/{post_ID}', 'showMinimal')->name('post_show_minimal');
    });
});

// Like Routes 
Route::prefix('like')->controller(LikeController::class)->group(function(){
    Route::get('/all', 'index')->name('like_all');
    Route::get('/{id}', 'show')->name('like_show');
    Route::post('/create/{postID}', 'store')->middleware('auth:sanctum')->name('like_create');
    Route::put('/update/{id}', 'update')->middleware('auth:sanctum')->name('like_update');
    Route::delete('/delete/{postID}', 'destroy')->middleware('auth:sanctum')->name('like_delete');
    Route::get('/userliked/{postID}', 'userliked')->middleware('auth:sanctum')->name('user_liked');
});

// User routes
Route::prefix('user')->group(function(){
    Route::controller(UserController::class)->group(function(){
        Route::get('/all', 'index')->name('user_all');
        Route::get('/{id}', 'show')->name('user_show');
        Route::get('/minimal/{id}', 'showMinimal')->name('user_show_minimal');
        Route::post('/create', 'store')->name('user_create');
        Route::post('/update/{id}', 'update')->middleware('auth:sanctum')->name('user_update');
        Route::delete('/delete/{id}', 'destroy')->middleware('auth:sanctum')->name('user_delete');
    });
});

//Follow routes
Route::prefix('follow')->controller(FollowController::class)->group(function(){
    Route::get('/all', 'index')->name('follow_all');
    Route::get('/status/{to_user_id}', 'show')->middleware('auth:sanctum')->name('follow_show');
    Route::post('/create/{to_user_id}', 'store')->middleware('auth:sanctum')->name('follow_create');
    Route::delete('/delete/{to_user_id}', 'destroy')->middleware('auth:sanctum')->name('follow_delete');
    Route::put('/update/{id}', 'update')->middleware('auth:sanctum')->name('follow_update');
    Route::get('/followers/{id}', 'followers')->name('followers');
    Route::get('/following/{id}', 'following')->name('following');
    Route::get('/requests/{id}', 'requests')->name('requests');
    Route::put('/accept/{id}', 'accept')->middleware('auth:sanctum')->name('accept');
    Route::put('/reject/{id}', 'reject')->middleware('auth:sanctum')->name('reject');
});