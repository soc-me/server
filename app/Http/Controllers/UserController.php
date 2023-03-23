<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    // Show the specified resource with minimal data
    public function showMinimal(string $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }
        // todo: get followers and following
        $response = [
            'id' => $user->id,
            'name' => $user->name,
            'created_at' => $user->created_at,
            'bio' => $user->bio,
        ];
        return response($response, 200);
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
            $count++;
        }
        return $objects;
    }
}
