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
        if(!Auth::user()){
            // get all the public accounts
            $publicAccounts = User::where('is_private', False)->get();
            // get posts by these accounts using a table join
            $postObjects = Post::join('users', 'posts.user_id', '=', 'users.id')
                ->whereIn('users.id', $publicAccounts->pluck('id'))
                ->orderBy('posts.created_at', 'desc')
                ->select('posts.*')
                ->get();
        }else{
            // get all public accounts 
            $publicAccounts = User::where('is_private', False)->get();
            // get all following objects of the user
            $followingObjects = Follow::where('from_user_id', Auth::user()->id)
                ->where('accepted', True)
                ->select('to_user_id')
                ->get();
            // get the following accoutns
            $followingAccounts = User::whereIn('id', $followingObjects->pluck('to_user_id'))->get();
            // get posts of these accounts + current users posts
            $allRequiredAccounts = $publicAccounts->merge($followingAccounts);
            $allRequiredAccounts->push(Auth::user());
            $postObjects = Post::join('users', 'posts.user_id', '=', 'users.id')
                ->whereIn('users.id', $allRequiredAccounts->pluck('id'))
                ->orderBy('posts.created_at', 'desc')
                ->select('posts.*')
                ->get();
        }
        // add post meta data
        $userController = new UserController();
        $likeController = new LikeController();
        $postObjects = $userController->addUserData($postObjects);
        foreach($postObjects as $postObject){
            // get the like count of each post
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
            // if the user is logged in, check if the posts are liked by the user
            if(Auth::user()){
                $userObject = Auth::user();
                $postObject['liked'] = $likeController->likeCheck($postObject->id, $userObject->id);
            }
        }

        //response
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
        // checks whether the user is private and if the user is following the user
        $currUserObject = Auth::user();
        if($userObject->is_private && $currUserObject !== $userObject){
            $followObject = Follow::where('from_user_id', $currUserObject->id)->where('to_user_id', $id)->where('accepted', True)->first();
            if(!$followObject){
                $response = [
                    'notAllowed' => true,
                ];
                return response($response, 200);
            }
        }
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
            'notAllowed' => false
        ];
        return response($response, 200);
    }

    // following users posts
    public function followingPosts(Request $request){
        $userObject = Auth::user();
        // getting the following users
        $followingObjects = Follow::where('from_user_id', $userObject->id)
            ->where('accepted', True)
            ->get();
        // getting the user objects of the following users
        $followingUserObjects = User::whereIn('id', $followingObjects->pluck('to_user_id'))->get();
        //adding the current user to the list
        $followingUserObjects->push($userObject);
        // table join
        $postObjects = Post::join('users', 'posts.user_id', '=', 'users.id')
            ->whereIn('users.id', $followingUserObjects->pluck('id'))
            ->select('posts.*')
            ->orderBy('posts.created_at', 'desc')
            ->get();
        // addding meta data
        $userController = new UserController();
        $postObjects = $userController->addUserData($postObjects);
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
        // check whether the user is private and if the user is following the user
        $userObject = User::where('id', $postObject->user_id)->first();
        if($userObject->is_private){
            $currUserObject = Auth::user();
            if(!$currUserObject){
                $response = [
                    'notAllowed' => true,
                ];
                return response($response, 200);
            }
            // check whether a follow objects exists
            $followObject = Follow::where('from_user_id', $currUserObject->id)
                ->where('to_user_id', $userObject->id)
                ->where('accepted', True)
                ->first();
            if(!$followObject){
                $response = [
                    'notAllowed' => true,
                ];
                return response($response, 200);
            }
        }
        // if not or if the user is following the user, add the meta data
        $userController = new UserController();
        $likeController = new LikeController();
        $postObject = $userController->addUserData([$postObject])[0];  
        $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
        $currUserObject = Auth::user();
        if($currUserObject){
            $postObject['liked'] = $likeController->likeCheck($postObject->id, $userObject->id);
        }
        //response
        $response = [
            'postObject' => $postObject
        ];
        return response($response, 200);
    }
}
