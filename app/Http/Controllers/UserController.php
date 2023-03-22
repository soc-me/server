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
