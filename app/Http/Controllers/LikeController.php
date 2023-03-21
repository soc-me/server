<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $fields = $request->validate([
            'post_id'=> 'required',
        ]);
        $fields['user_id'] = $request->user()->id;
        //checking whether a like already exists
        $likeObject = Like::where('user_id', $fields['user_id'])->where('post_id', $fields['post_id'])->first();
        if($likeObject){
            return response([
                'status' => 'duplicate'
            ], 201);
        }
        //else 
        $likeObject = Like::create($fields);
        return response([
            'likeObject' => $likeObject
        ], 201);
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
    public function destroy(string $userID)
    {
        $likeObject = Like::where('user_id', $userID)->first();
        $likeObject->delete();
        return response([
            'status' => 'deleted'
        ], 200);
    }

    //Get likes on a post
    public function likesOnPost(string $postID)
    {
        $likeObjects = Post::find($postID)->likeList()->get();
        return response([
            'likeObjects' => $likeObjects
        ], 200);
    }
}
