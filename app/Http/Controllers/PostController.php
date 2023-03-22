<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\UserController;

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
        $response = [
            'posts' => $postObjects
        ];
        return response($response, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'content' => ['required', 'string', 'max:500'],
        ]);
        $fields['user_id'] = Auth::user()->id;
        $postObject = Post::create($fields);
        $userController = new UserController();
        $postObjects = $userController->addUserData([$postObject]);  // this function requires an array
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
}
