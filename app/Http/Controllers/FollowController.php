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
        $follow->accepted = True;  //remove
        $follow->save();
        // recalculate the follower and following count of the users
        $this->followCalculator($follow->to_user_id);
        $this->followCalculator($follow->from_user_id);
        //temp =====
        return response()->json([
            'response' => 'following'  //change to 'null' 
        ], 200);
    }

    //Accept a follow request
    public function accept(Request $request, string $followID)
    {
        $followObject = Follow::find('id', $followID)->first();
        if($followObject->to_user_id != Auth::user()->id || $followObject->accepted == True || !$followObject){
            return response()->json([
                'message' => 'You are not authorized to accept this follow request',
                'completion' => False
            ], 400);
        }
        $followObject->accepted = True;
        $followObject->save();
        // recalculate the follower and following count of the users
        $this->followCalculator($followObject->to_user_id);
        $this->followCalculator($followObject->from_user_id);
        //response
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
        // delete first!
        $followObject->delete();
        // recalculate the follower and following count of the users
        $this->followCalculator($followObject->to_user_id);
        $this->followCalculator($followObject->from_user_id);
        //response
        return response()->json([
            'response' => "null",
        ], 200);
    }

    //helper function: recalculate the follower and following count of a user
    public function followCalculator(string $user_id){
        $userObject = User::find($user_id);
        $userObject->followerCount = Follow::where('to_user_id', $user_id)->count();
        $userObject->followingCount = Follow::where('from_user_id', $user_id)->count();
        $userObject->save();
    }
}
