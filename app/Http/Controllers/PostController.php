<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;
use App\Models\Follow;
use App\Models\User;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $postObjects = Post::orderBy('created_at', 'desc')->get();
        $userController = new UserController();
        $postObjects = $userController->addUserData($postObjects);
        $likeController = new LikeController();
        foreach($postObjects as $postObject){
            // get the like count of each post
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
            // if the user is logged in, check if the posts are liked by the user
            if(Auth::user()){
                $userObject = Auth::user();
                $postObject['liked'] = $likeController->likeCheck($postObject->id, $userObject->id);
            }
        }
        $response = [
            'postObjects' => $postObjects
        ];
        return response($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'content' => ['required', 'string'],
        ]);
        $fields['user_id'] = Auth::user()->id;
        $fields['likeCount'] = 0;
        $postObject = Post::create($fields);
        $userController = new UserController();
        $postObjects = $userController->addUserData([$postObject]);  // this function requires an array and returns one
        // checks whether the user has liked the post and adds the like count
        $likeController = new LikeController();
        $postObjects[0]['liked'] = $likeController->likeCheck($postObjects[0]->id, $fields['user_id']);
        $postObjects[0]['likeCount'] = $likeController->calculateLikes($postObjects[0]->id);
        $response = [
            'status'=> 'OK',
            'postObject'=> $postObjects[0]
        ];
        return response($response, 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reponse = [
            'postData' => Post::find($id)
        ];
        return response($reponse, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Checking whether the current user is the object owner
        $postObject = Post::find($id);
        $userID = Auth::user()->id;
        if($postObject.value('createdByUser_Key')!=$userID){
            return response(401);
        }
        //Updating the table
        $fields = $request->validate([
            'content' => ['required', 'string', 'max:500']
        ]);  
        $postObject['content'] = $fields['content'];
        $postObject->save();
        $response = [
            'status' => 'OK',
            'postObject' => $postObject
        ];
        return response($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //Checking whether the current user is the object owner
        $postObject = Post::find($id);
        $userID = Auth::user()->id;
        if($postObject.value('createdByUser_Key')!=$userID){
            return response(401);
        }
        // Deleting the object
        $postObject->delete();
        $response = [
            'status'=> 'OK'
        ];
        return response($response, 200);
    }

    // Test function
    public function test(Request $request){
        $response = [
            'status' => 'OK'
        ];
        return response($response, 200);
    }

    //Get posts of user
    public function postsByUser(string $id){
        $userObject = User::find($id);
        $postObjects = $userObject->postList()->orderBy('created_at', 'desc')->get();
        $userController = new UserController();
        $postObjects = $userController->addUserData($postObjects);
        // checks whether the user has liked the post and adds the like count
        $likeController = new LikeController();
        foreach($postObjects as $postObject){
            $postObject['liked'] = $likeController->likeCheck($postObject->id, $id);
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
        }     
        $response = [
            'postObjects' => $postObjects,
            'private' => false
        ];
        return response($response, 200);
    }

    // following users posts
    public function followingPosts(Request $request){
        $userObject = Auth::user();
        $followingObjects = Follow::where('from_user_id', $userObject->id)->get();
        // table join
        $postObjects = Post::join('users', 'posts.user_id', '=', 'users.id')
            ->whereIn('users.id', $followingObjects->pluck('to_user_id'))
            ->select('posts.*')
            ->orderBy('posts.created_at', 'desc')
            ->get();
            // join function arguments: table name, column name, operator, column name
        $userController = new UserController();
        $postObjects = $userController->addUserData($postObjects);
        // checks whether the user has liked the post and adds the like count
        $userObject = Auth::user();
        $likeController = new LikeController();
        foreach($postObjects as $postObject){
            $postObject['liked'] = $likeController->likeCheck( $postObject->id, $userObject->id);
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
        }
        $response = [
            'postObjects' => $postObjects,
        ];
        return response($response, 200);
    }

    // Show minimal post data
    public function showMinimal(Request $request, string $post_ID)
    {
        // find post with post id
        $postObject = Post::where('id', $post_ID)->first();
        $userController = new UserController();
        $postObject = $userController->addUserData([$postObject])[0];  // this function requires an array and returns one
        // checks whether the user has liked the post and adds the like count
        $userObject = Auth::user();
        $likeController = new LikeController();
        if($userObject){
            $postObject['liked'] = $likeController->likeCheck($postObject->id, $userObject->id);
        }
        $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
        $response = [
            'postObject' => $postObject
        ];
        return response($response, 200);
    }
}
