<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response([
            'likeObjects' => Like::all()
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $postID)
    {
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
        // calculate likes on the post  -> removed since will be calculated on the fly
        // $this->calculateLikes($fields['post_id']);
        return response([
            'likeObject' => $likeObject
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        return response([
            'status' => 'todo'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $postID)
    {
        $likeObject = Like::where('user_id', Auth::user()->id)
            ->where('post_id', $postID)
            ->get();
        foreach($likeObject as $like){
            $like->delete();
        }
        // calculate likes on the post -> removed since will be calculated on the fly
        // $this->calculateLikes($likeObject->post_id);
        return response([
            'status' => 'deleted'
        ], 200);
    }

    //Checks whether a user has liked the post===== deprecated
    public function userLiked(string $postID)
    {
        $userObject = Auth::user();
        $likeObjects = Like::where('post_id', $postID)
            ->where('user_id', $userObject->id)
            ->get();
        //if the user has liked the post
        if($likeObjects->count() > 0){
            return response([
                'liked' => true
            ], 200);
        }else{
            return response([
                'liked' => false
            ], 200);
        }
    }

    //helper function: calculate likes on a post
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
