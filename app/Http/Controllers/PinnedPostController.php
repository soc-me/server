<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\PinnedPost;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PinnedPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Note that you can't pin private posts so we don't need to check for that
        $pinnedObjects = PinnedPost::all();
        $pinnedPosts = Post::whereIn('id', $pinnedObjects->pluck('post_id'))->orderBy('created_at', 'desc')->get();
        // add post meta data
        $userController = new UserController();
        $likeController = new LikeController();
        $postObjects = $userController->addUserData($pinnedPosts);
        foreach($postObjects as $postObject){
            // get the like count of each post
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
            // if the user is logged in, check if the posts are liked by the user
            if(Auth::user()){
                $userObject = Auth::user();
                $postObject['liked'] = $likeController->likeCheck($postObject->id, $userObject->id);
            }
            // get comments on post
            $commentCount = count(Comment::where('post_id', $postObject->id)->get());
            $postObject['commentCount'] = $commentCount;
            // checking whether the posts owner has a private account
            $privateStatus = User::find($postObject->user_id)->is_private;
            $postObject['privateStatus'] = $privateStatus;
        }
        $response = [
            'message' => 'All pinned posts',
            'objects' => $postObjects
        ];
        return response($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $post_id)
    {
        // check if user is not an admin
        if (Auth::user()->isAdmin == false) {
            return response(['message' => 'Unauthorized'], 401);
        }
        // check if post is private
        if (Post::where('id', $post_id)->first()->isPrivate == true) {
            return response(['message' => 'Cannot pin private post'], 400);
        }
        $pinnedPost = new PinnedPost();
        $pinnedPost->post_id = $post_id;
        $pinnedPost->save();
        return response(['message' => 'Post pinned'], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
    public function destroy(string $post_id)
    {
        // check if user is not an admin
        if (Auth::user()->isAdmin == false) {
            return response(['message' => 'Unauthorized'], 401);
        }
        // remove pinned post if it exists
        $pinnedPost = PinnedPost::where('post_id', $post_id)->first();
        if ($pinnedPost != null) {
            $pinnedPost->delete();
            return response(['message' => 'Post unpinned'], 200);
        }else{
            return response(['message' => 'Post not pinned'], 400);
        }
    }
}
