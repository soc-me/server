<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\ImageManagerStatic as Image;

class UserController extends Controller
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
    public function store(Request $request)
    {
        // REFER: AUTH/REGISTER
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //REFER: /USER
    }

    // Show the specified resource with minimal data
    public function showMinimal(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }
        // get followers and following
        $followController = new FollowController();
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'created_at' => $user->created_at,
            'bio' => $user->bio,
            'imageURL' => $user->imageURL,
            'followers' => $followController->followers_Calculator($id),
            'following' => $followController->following_Calculator($id),
            'postCount' => Post::where('user_id', $id)->count(),
        ];
        return response($response, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //make sure that current user is the user that is being updated
        if ($id != Auth::user()->id) {
            return response(['message' => 'Unauthorized'], 401);
        }
        $fields = $request->validate([
            'bio' => ['string', 'max:500'],
            'image' => ['image', 'max:2048', 'mimes:jpeg,png,jpg,gif,webp'],
        ]);
        $user = User::find($id);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            // $image = Image::make($image->getRealPath());
            // $image->resize(300, 300, function ($constraint) {
            //     $constraint->aspectRatio();
            // });
            $name = time() . '.' . $image->getClientOriginalExtension();
            $destinationPath = public_path('/public/user/');
            $image->move($destinationPath, $name);
            $fields['imageURL'] = '/public/user/' . $name;
            $user->imageURL = $fields['imageURL'];
        }
        $extra = [];
        if (isset($fields['bio'])) {
            $user->bio = $fields['bio'];
            $extra['bio'] = $fields['bio'];
        }
        $user->save();
        return response(['message' => 'User updated', 'extra' => $fields], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    //Add user data to objects
    public function addUserData($objects)
    {
        $count = 0;
        foreach ($objects as $object) {
            // add the 'name' of the user to the object by using the 'user_id' of the object
            $objects[$count]['name'] = User::find($object['user_id'])->name;
            $objects[$count]['imageURL'] = User::find($object['user_id'])->imageURL;
            $count++;
        }
        return $objects;
    }
}
