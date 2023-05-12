<?php

namespace App\Http\Controllers;

use App\Models\PinnedPost;
use App\Models\Post;
use Illuminate\Http\Request;

class PinnedPostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Note that you can't pin private posts so we don't need to check for that
        $pinnedObjects = PinnedPost::all();
        $pinnedPosts = Post::whereIn('id', $pinnedObjects->pluck('post_id'))->get();
        $response = [
            'message' => 'All pinned posts',
            'objects' => $pinnedPosts
        ];
        return response($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
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
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
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
}
