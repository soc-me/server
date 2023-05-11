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
     * Returns all necessary data for the home page ================================================
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
            // get the following accounts
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
        //
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
            'postObjects' => $postObjects,
        ];
        return response($response, 200);
    }

    /**
     * Store a newly created resource in storage. ================================================
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
        $postObjects = $userController->addUserData([$postObject]);  // this function requires an array and returns an array
        // checks whether the user has liked the post and adds the like count
        $likeController = new LikeController();
        $postObjects[0]['liked'] = $likeController->likeCheck($postObjects[0]->id, $fields['user_id']);
        $postObjects[0]['likeCount'] = $likeController->calculateLikes($postObjects[0]->id);
        $response = [
            'status'=> 'OK',
            'returnObject'=> $postObjects[0]
        ];
        return response($response, 200);
    }

    /**
     * Display the specified resource. ================================================
     */
    public function show(string $post_ID)
    {
        //find post id
        $postObject = Post::find($post_ID);
        // find user who created the post
        $userObject = User::find($postObject->user_id);
        // current user
        $currUserObject = Auth::user();
        // user perms check
        if($userObject->is_private && $userObject != $currUserObject){
            // if private and the user is not logged in return an err
            if(!$currUserObject){
                $reponse  = [
                    'notAllowed' => true,
                    'message' => 'You are not logged in'
                ];
                return response($reponse, 401);
            }
            $followObject = Follow::where('from_user_id', $currUserObject->id)
                ->where('to_user_id', $userObject->id)
                ->where('accepted', True)
                ->first();
            // if a follow object is not found, return an err
            if(!$followObject){
                $reponse  = [
                    'notAllowed' => true,
                    'message' => 'You are not following this user'
                ];
                return response($reponse, 401);
            }
        }
        // add meta data
        $userController = new UserController();
        $likeController = new LikeController();
        $postObject = $userController->addUserData([$postObject])[0];
        $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
        if($currUserObject){
            $postObject['liked'] = $likeController->likeCheck($postObject->id, $currUserObject->id);
        }
        $response = [
            'postObject' => $postObject
        ];
        return response($response, 200);
    }

    /**
     * Update the specified resource in storage. ================================================   
     */
    public function update(Request $request, string $id)
    {
        //Checking whether the current user is the object owner
        $postObject = Post::find($id);
        $userID = Auth::user()->id;
        if($postObject.value('user_id')!=$userID){
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
     * Remove the specified resource from storage. ================================================
     */
    public function destroy(Request $request, string $post_id, string $asAdmin)
    {
        //Checking whether the current user is the object owner
        $postObject = Post::find($post_id);
        $userID = Auth::user()->id;
        if(!$postObject){
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
            $postObject->delete();
            $response = [
                'status' => 'OK'
            ];
            return response($response, 200);
        }
        else if($postObject->user_id == $userID && $asAdmin==False){
            $postObject->delete();
            $response = [
                'status' => 'OK'
            ];
            return response($response, 200);
        }
        else{
            $response = [
                'status' => 'Not Allowed'
            ];
            return response($response, 401);
        }
    }

    /**
     * Get all the posts of a user ================================================
     */
    public function postsByUser(string $id){
        $userObject = User::find($id);
        $currUserObject = Auth::user();
        if($userObject->is_private && $currUserObject != $userObject){
            // if the user is not logged in, return an err
            if(!$currUserObject){
                $response = [
                    'notAllowed' => true,
                    'message' => 'You are not logged in'
                ];
                return response($response, 401);
            }
            // if the user is not following the user, return an err
            $followObject = Follow::where('from_user_id', $currUserObject->id)
                ->where('to_user_id', $id)
                ->where('accepted', True)
                ->first();
            if(!$followObject){
                $response = [
                    'notAllowed' => true,
                    'message' => 'You are not following this user',
                ];
                return response($response, 200);
            }
        }
        $postObjects = $userObject->postList()->orderBy('created_at', 'desc')->get();
        // add meta data
        $userController = new UserController();
        $likeController = new LikeController();
        $postObjects = $userController->addUserData($postObjects);
        foreach($postObjects as $postObject){
            if($currUserObject){
                $postObject['liked'] = $likeController->likeCheck($postObject->id, $currUserObject->id);
            }
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
        }     
        $response = [
            'postObjects' => $postObjects,
        ];
        return response($response, 200);
    }

    /**
     * Get following user's posts ================================================
     */
    public function followingPosts(Request $request){
        $userObject = Auth::user();
        // getting the following users
        $followingObjects = Follow::where('from_user_id', $userObject->id)
            ->where('accepted', True)
            ->get();
        // getting the user objects from the following objects
        $followingUserObjects = User::whereIn('id', $followingObjects->pluck('to_user_id'))->get();
        //adding the current user to the list
        $followingUserObjects->push($userObject);
        // getting the posts of the following users + the current user
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

    /**
     * Returns minimal data about a post (for SEO) ================================================
     */
    public function showMinimal(Request $request, string $post_ID)
    {
        // find post with post id
        $postObject = Post::where('id', $post_ID)->first();
        // if the post does not exist, return a message saying that the post does not exist
        if(!$postObject){
            $response = [
                'postObject' => null,
                'is_private' => null,
            ];
            return response($response, 404);
        }
        // find user with user id
        $userObject = User::where('id', $postObject->user_id)->first();
        // if the users account is not private, return the post
        if(!$userObject->is_private){
            // add meta data
            $userController = new UserController();
            $postObject = $userController->addUserData([$postObject])[0];
            $likeController = new LikeController();
            $postObject['likeCount'] = $likeController->calculateLikes($postObject->id);
            // The page title will be the first 15 charachters from the html
            $pageTitle = substr(strip_tags($postObject->content), 0, 20);
            $response = [
                'postObject' => $postObject,
                'is_private' => false,
                'pageTitle' => $pageTitle,
            ];
            return response($response, 200);
        }else{
            //return a message saying that the post is private
            $response = [
                'postObject' => [
                    'id' => $postObject->id,
                ],
                'is_private' => true,
            ];
        }
        return response($response, 200);
    }
}
