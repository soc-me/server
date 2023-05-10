<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Follow;
use App\Models\Post;
use App\Models\User;
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
        // Check whether the user is private. If so, user must be following the poster
        $postObject = Post::find($postID);
        $userObject = User::find($postObject->user_id);
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
        $commentObject = Comment::create($fields);
        //Add user data
        $userController = new UserController();
        $commentObject = $userController->addUserData([$commentObject])[0];
        return response([
            'returnObject' => $commentObject,
        ], 201);
    }

    /**
     * Get the commments of a post ==========================================================
    */
    public function commentsByPost(string $postID)
    {
        $commentObjects = Comment::where('post_id', $postID)->orderBy('created_at', 'asc')->get();
        //Add user data
        $userController = new UserController();
        foreach($commentObjects as $commentObject){
            $commentObject = $userController->addUserData([$commentObject])[0];
        }
        if($commentObjects->isEmpty()){
            return response(['noComments' => True], 404);
        }
        return response([
            'commentObjects' => $commentObjects
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $comment_ID, string $asAdmin)
    {
        //Checking whether the current user is the object owner
        $commentObject = Comment::find($comment_ID);
        $userID = Auth::user()->id;
        if(!$commentObject){
            $response = [
                'status' => 'Not Found'
            ];
            return response($response, 404);
        }
        //Get asAdmin from the request
        if($asAdmin=='true'){
            $asAdmin = True;
        }
        else if ($asAdmin=='false'){
            $asAdmin = False;
        }
        if($asAdmin && Auth::user()->isAdmin){
            $commentObject->delete();
            $response = [
                'status' => 'OK',
            ];
            return response($response, 200);
        }
        else if($commentObject->user_id == $userID && $asAdmin==false){
            $commentObject->delete();
            $response = [
                'status' => 'OK',
            ];
            return response($response, 200);
        }
        else{
            $response = [
                'status' => 'Unauthorized'
            ];
            return response($response, 401);
        }
    }
}
