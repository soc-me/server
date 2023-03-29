<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource. =======================================================
     */
    public function index()
    {
        return response([
            'likeObjects' => Like::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage. =======================================================
     */
    public function store(Request $request, string $postID)
    {
        // getting the user object of the creator
        $post = Post::find($postID);
        if(!$post){
            return response([
                'status' => 'post not found'
            ], 404);
        }
        $userObject = User::where('id', $post->user_id)->first();
        // if the user's account is private, checking whether the user is following the creator
        if($userObject->private){
            $followObject = Follow::where('to_user_id', $userObject->id)
                ->where('from_user_id', Auth::user()->id)
                ->first();
            if(!$followObject){
                return response([
                    'notAllowed' =>true,
                    'message' => 'You are not following the owner of this post'
                ], 401);
            }
        }
        //checking whether a like already exists
        $likeObject = Like::where('user_id', Auth::user()->id)->where('post_id', $postID)->first();
        if($likeObject){
            return response([
                'status' => 'duplicate'
            ], 201);
        }
        //else 
        $likeObject = Like::create([
            'user_id' => Auth::user()->id,
            'post_id' => $postID
        ]);
        return response([
            'likeObject' => $likeObject
        ], 200);
    }

    /**
     * Display the specified resource. =======================================================
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage. =======================================================
     */
    public function update(Request $request, string $id)
    {
        return response([
            'status' => 'todo'
        ], 200);
    }

    /**
     * Remove the specified resource from storage. =======================================================
     */
    public function destroy(Request $request, string $postID)
    {
        $likeObject = Like::where('user_id', Auth::user()->id)
            ->where('post_id', $postID)
            ->get();
        foreach($likeObject as $like){
            $like->delete();
        }
        return response([
            'status' => 'deleted'
        ], 200);
    }

    //helper function: calculate likes on a post    =======================================================
    public function calculateLikes(string $postID)
    {
        return Like::where('post_id', $postID)->count();
    }

    //helper function: check whether user has liked the post
    public function likeCheck(string $postID, string $userID)
    {
        $likeObjects = Like::where('post_id', $postID)
            ->where('user_id', $userID)
            ->get();
        //if the user has liked the post
        if($likeObjects && $likeObjects->count() > 0){
            return true;
        }else{
            return false;
        }
    }
}
