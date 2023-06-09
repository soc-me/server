<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PinnedPostController;
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
        Route::delete('/delete/{post_id}/{asAdmin}', 'destroy')->middleware('auth:sanctum')->name('post_delete');
        Route::get('/user/{id}', 'postsByUser')->name('posts_by_user');
        Route::get('/minimal/{post_ID}', 'showMinimal')->name('post_show_minimal');
        Route::get('/{post_ID}', 'show')->name('post_show');
        Route::post('/search', 'search')->name('post_search');
        Route::get('/page_title/{post_ID}', 'getPageTitle')->name('post_page_title');
        Route::get('/community/{community_id}', 'communityPosts')->name('posts_by_community');
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
        Route::post('/search', 'search')->name('user_search');
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
    Route::put('/accept/{from_user_id}', 'accept')->middleware('auth:sanctum')->name('accept');
    Route::put('/reject/{id}', 'reject')->middleware('auth:sanctum')->name('reject');
    Route::get('/followrequests_count/{user_id}', 'getFollowRequestCount')->name('followrequests_count');
    Route::get('/pendingrequests/{user_id}', 'getPendingRequests')->name('pendingrequests');
});

// Commment routes
Route::prefix('comment')->controller(CommentController::class)->group(function(){
    Route::get('/all', 'index')->name('comment_all');
    Route::get('/{id}', 'show')->name('comment_show');
    Route::post('/create/{postID}', 'store')->middleware('auth:sanctum')->name('comment_create');
    Route::put('/update/{id}', 'update')->middleware('auth:sanctum')->name('comment_update');
    Route::delete('/delete/{comment_ID}/{asAdmin}', 'destroy')->middleware('auth:sanctum')->name('comment_delete');
    Route::get('/post/{postID}', 'commentsByPost')->name('comments_by_post');
});

//Notification routes
Route::prefix('notification')->controller(NotificationController::class)->group(function(){
    Route::get('/my_notifications', 'index')->middleware('auth:sanctum')->name('notification_all');
    Route::get('/read/{notification_id}', 'read')->middleware('auth:sanctum')->name('notification_read');
    Route::put('/read_all', 'readAll')->middleware('auth:sanctum')->name('notification_read_all');
    Route::get('/unread_count', 'unreadCount')->middleware('auth:sanctum')->name('notification_unread_count');
});

// Pinned Post routes
Route::prefix('pinned_post')->controller(PinnedPostController::class)->group(function(){
    Route::get('/posts', 'index')->name('pinned_post_all');
    Route::post('/pin/{post_id}', 'store')->middleware('auth:sanctum')->name('pinned_post_create');
    Route::delete('/remove/{post_id}', 'destroy')->middleware('auth:sanctum')->name('pinned_post_delete');
});

// Community routes
Route::prefix('community')->controller(CommunityController::class)->group(function(){
    Route::get('/all', 'index')->name('community_all');
    Route::get('/data/{id}', 'show')->name('community_show');
    Route::post('/create', 'store')->middleware('auth:sanctum')->name('community_create');
    Route::post('/update/{id}', 'update')->middleware('auth:sanctum')->name('community_update');
    Route::delete('/delete/{id}', 'destroy')->middleware('auth:sanctum')->name('community_delete');
    Route::post('/search', 'search')->name('community_search');
});