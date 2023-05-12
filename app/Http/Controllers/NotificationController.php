<?php

namespace App\Http\Controllers;

use App\Models\Notification_Curr;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notification;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user_id = $request->user()->id;
        $notifications = Notification_Curr::where('notification_of_user_id', $user_id)->get();
        $response = [
            'message' => 'All notifications',
            'notificationObjects' => $notifications
        ];
        return response($response, 200);
    }

    /**
     * Show the form for creating a new resource
     * This is not publicly accessible
     */
    public function create(
        string $notification_of_user_id,
        string $notification_from_user_id,
        string $on_post_id,
        string $message,
        string $type
    )
    {
        $notificationObject = new Notification_Curr();
        $notificationObject->notification_of_user_id = $notification_of_user_id;
        $notificationObject->notification_from_user_id = $notification_from_user_id;
        $notificationObject->on_post_id = $on_post_id;
        $notificationObject->message = $message;
        $notificationObject->type = $type;
        $notificationObject->save();
        return $notificationObject;
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
