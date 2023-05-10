<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage ===========================================
     */
    public function store(Request $request, string $postID)
    {
        $fields = $request->validate([
            'content' => 'required|string|max:255',
        ]);
        $fields['user_id'] = Auth::user()->id;   
        $fields['post_id'] = $postID;
        $commentObject = Comment::create($fields);
        return response($commentObject, 201);
    }

    /**
     * Get the commments of a post ==========================================================
    */
    public function commentsByPost(string $postID)
    {
        $comments = Comment::where('post_id', $postID)->get();
        return response($comments, 200);
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $comment_ID)
    {
        $currUserObject = Auth::user();
        if($currUserObject->id != Comment::find($comment_ID)->user_id){
            return response(['message' => 'Unauthorized'], 401);
        }
        Comment::destroy($comment_ID);
        return response(['message' => 'Comment deleted'], 200);
    }
}
