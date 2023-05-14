<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $objects = Community::orderBy('created_at', 'desc')->get();
        $response = [
            'message' => 'List of all communities',
            'data' => $objects
        ];
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = [
            'community_name' =>['required', 'string', 'max:20', 'min:2', 'unique:communities'],
            'community_description' => ['required', 'string', 'max:255'],
            'community_icon_image_url' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'community_banner_image_url' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'hide_posts_from_home' => 'required|boolean'
        ];
        $newObject = Community::create($fields);
        $newObject->owner_user_id = Auth::user()->id;
        $newObject->save();
        $response = [
            'message' => 'Community created successfully',
            'communityObject' => $newObject
        ];
        return response()->json($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $object = Community::find($id);
        if (!$object) {
            return response(['message' => 'Community not found'], 404);
        }
        $response = [
            'message' => 'Community details by ID',
            'data' => $object
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(string $id)
    {
        $object = Community::find($id);
        //check if user is admin or owner
        if(!Auth::user()->is_admin && Auth::user()->id != $object->owner_user_id){
            return response(['message' => 'You are not authorized to edit this community'], 401);
        }
        $fields = [
            'community_name' =>[ 'string', 'max:20', 'min:2', 'unique:communities'],
            'community_description' => [ 'string', 'max:255'],
            'community_icon_image_url' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'community_banner_image_url' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'hide_posts_from_home' => 'boolean'
        ];
        if(isset($fields['community_name'])){
            $object->community_name = $fields['community_name'];
        }
        if(isset($fields['community_description'])){
            $object->community_description = $fields['community_description'];
        }
        if(isset($fields['community_icon_image_url'])){
            $object->community_icon_image_url = $fields['community_icon_image_url'];
        }
        if(isset($fields['community_banner_image_url'])){
            $object->community_banner_image_url = $fields['community_banner_image_url'];
        }
        if(isset($fields['hide_posts_from_home'])){
            $object->hide_posts_from_home = $fields['hide_posts_from_home'];
        }
        $object->save();
        $response = [
            'message' => 'Community updated successfully',
            'communityObject' => $object
        ];
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $object = Community::find($id);
        if(!Auth::user()->is_admin && Auth::user()->id != $object->owner_user_id){
            return response(['message' => 'You are not authorized to delete this community'], 401);
        }
        $object = Community::find($id);
        if (!$object) {
            return response(['message' => 'Community not found'], 404);
        }
        $object->delete();
        $response = [
            'message' => 'Community deleted successfully',
            'communityObject' => $object
        ];
        return response()->json($response, 200);
    }

    /**
    * Search for a community by name
    */
    public function search(Request $request)
    {
        $searchQuery = $request->input('query');
        $object = Community::where('community_name', 'LIKE', '%'.$searchQuery['community_name'].'%')->get();
        $response = [
            'message' => 'Community details by name',
            'objects' => $object
        ];
        return response()->json($response, 200);
    }
}
