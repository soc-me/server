<?php

namespace App\Http\Controllers;

use App\Models\Community;
use App\Models\Post;
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
            'objects' => $objects
        ];
        $withPosts = [];
        $withoutPosts = [];
        // for each object get the time of the last post
        foreach($objects as $object){
            $lastPost = Post::where('community_id', $object->id)->orderBy('created_at', 'desc')->first();
            if($lastPost){
                $object->last_post_time = $lastPost->created_at;
                array_push($withPosts, $object);
            }else{
                $object->last_post_time = null;
                array_push($withoutPosts, $object);
            }
        }
        usort($withPosts, function($a, $b) {
            return $a->last_post_time < $b->last_post_time;
        });
        $response['objects'] = array_merge($withPosts, $withoutPosts);
        return response()->json($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'community_name' =>['required', 'string', 'max:20', 'min:2', 'unique:communities'],
            'community_description' => ['required', 'string', 'max:255', 'min:2'],
            // 'community_icon_image_url' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            // 'community_banner_image_url' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'hide_posts_from_home' => ['string', 'max:6', 'min:4', 'in:HIDE,UNHIDE', 'required']
        ]);
        if($fields['hide_posts_from_home'] == 'HIDE'){
            $fields['hide_posts_from_home'] = true;
        }else if($fields['hide_posts_from_home'] == 'UNHIDE'){
            $fields['hide_posts_from_home'] = false;
        }
        $fields['owner_user_id'] = Auth::user()->id;
        $newObject = Community::create($fields);
        $response = [
            'message' => 'Community created successfully',
            'object' => $newObject
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
            'object' => $object
        ];
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function update(string $id, Request $request)
    {
        $object = Community::find($id);
        //check if user is admin or owner
        if(!Auth::user()->isAdmin && Auth::user()->id != $object->owner_user_id){
            return response(['message' => 'You are not authorized to edit this community'], 401);
        }
        $fields = [
            'community_name' =>[ 'string', 'max:20', 'min:2', 'unique:communities'],
            'community_description' => [ 'string', 'max:255'],
            'iconImage' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'bannerImage' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
            'hide_posts_from_home' => 'boolean'
        ];
        if($request->has('community_name')){
            $object->community_name = $fields['community_name'];
        }
        if($request->has('community_description')){
            $object->community_description = $fields['community_description'];
        }
        if($request->has('iconImage')){
            $image = $request->file('iconImage');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/public/community/icons/');
            $image->move($destinationPath, $name);
            $imageURL = '/public/community/icons/' . $name;
            $object->community_icon_image_url = $imageURL;
        }
        if($request->has('bannerImage')){
            $image = $request->file('bannerImage');
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/public/community/banners/');
            $image->move($destinationPath, $name);
            $imageURL = '/public/community/banners/' . $name;
            $object->community_banner_image_url = $imageURL;
        }
        if($request->has('hide_posts_from_home')){
            $object->hide_posts_from_home = $fields['hide_posts_from_home'];
        }
        $object->save();
        $response = [
            'message' => 'Community updated successfully',
            'object' => $object
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
            'object' => $object
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
