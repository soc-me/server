<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $to_user_id)
    {
        $from_user_id = Auth::user()->id;
        $hasRequested = Follow::where('from_user_id', $from_user_id)
            ->where('to_user_id', $to_user_id)
            ->first();
        if($hasRequested){
            return response()->json([
                'message' => 'You have already followed this user',
                'completion' => False
            ], 400);
        }
        $follow = new Follow();
        $follow->to_user_id = $to_user_id;
        $follow->from_user_id = $from_user_id;
        // check whether the users profile is private and set the accepted status accordingly
        $to_userObject = User::find($to_user_id);
        if($to_userObject->is_private == True){
            $follow->accepted = False;
        }else{
            $follow->accepted = True;
        }
        $follow->save();
        // get the follows count of the user
        $follows_count = $this->following_Calculator($to_user_id);
        return response()->json([
            'response' => ($to_userObject->is_private == True) ? 'requested' : 'following',
            'followCount' => $follows_count
        ], 200);
    }

    //Accept a follow request
    public function accept(Request $request, string $from_user_id)
    {
        $from_user_id = intval($from_user_id);
        $followObject = Follow::where('from_user_id', $from_user_id)
            ->where('to_user_id', Auth::user()->id)
            ->where('accepted', False)
            ->first();
        if($followObject->accepted == True || !$followObject){
            return response()->json([
                'message' => 'You are not authorized to accept this follow request',
                'completion' => False
            ], 400);
        }
        $followObject->accepted = True;
        $followObject->save();
        return response()->json([
            'response' => 'following'
        ], 200);
    }

    /**
     * Display the specified resource and its status
     */
    public function show(Request $request, string $to_user_id)
    {
        $from_user_id = Auth::user()->id;
        $followObject = Follow::where('from_user_id', $from_user_id)
            ->where('to_user_id', $to_user_id)
            ->first();
        if(!$followObject){
            return response()->json([
                'resopnse' => 'null'  // null is when the user is not following and has not requested to follow
            ], 200);
        }
        if($followObject->accepted == True){
            return response()->json([
                'response' => 'following'
            ], 200);
        }else{
            return response()->json([
                'response' => 'requested'
            ], 200);
        }
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
    public function destroy(Request $request, string $to_user_id)
    {
        $from_user_id = Auth::user()->id;
        $followObject = Follow::where('from_user_id', $from_user_id)
            ->where('to_user_id', $to_user_id)
            ->first();
        if(!$followObject){
            return response()->json([
                'message' => 'Does not exists'
            ], 404);
        }
        $followObject->delete();
        // get the follows count of the user
        $follows_count = $this->followers_Calculator($to_user_id);
        //response
        return response()->json([
            'response' => "null",
            'followCount' => $follows_count
        ], 200);
    }
    
    /**
    * Get the number of follow requests of a user
    */
    public function getFollowRequestCount(Request $request, string $user_id){
        $currUser = Auth::user();
        if($currUser->id != $user_id || $currUser->is_private == False){
            return response()->json([
                'message' => !$currUser->is_private ? 'Not a private account' : 'You are not authorized to view this' 
            ], 400);
        }
        $response = [
            'requestCount' => Follow::where('to_user_id', $user_id)->where('accepted', False)->count()
        ];
        return response()->json($response, 200);
    }

    /**
    * Get a user's pending follow requests
    */
    public function getPendingRequests(string $user_id)
    {
        $currUser = Auth::user();
        if($currUser->id != $user_id || $currUser->is_private == False){
            return response()->json([
                'message' => !$currUser->is_private ? 'Not a private account' : 'You are not authorized to view this' 
            ], 400);
        }
        // get the users where the current user is the to_user_id and the request is not accepted
        $userObjects = Follow::where('to_user_id', $user_id)->where('accepted', False)
            ->join('users', 'users.id', '=', 'follows.from_user_id')
            ->orderBy('follows.created_at', 'desc')
            ->select('users.*')
            ->get();

        $response = [
            'userObjects' => $userObjects
        ];
        return response($response, 200);
    }

    //helper function: recalculate the follower and following count of a user
    public function followers_Calculator(string $user_id){
        return Follow::where('to_user_id', $user_id)->count();
    }
    public function following_Calculator(string $user_id){
        return Follow::where('from_user_id', $user_id)->count();
    }
}
