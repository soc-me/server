<?php

namespace App\Http\Controllers;

use App\Models\Notification_Curr;
use App\Models\User;
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
        $notifications = Notification_Curr::where('notification_of_user_id', $user_id)->orderBy('created_at', 'desc')->get();
        // Add user image url to each notification
        foreach ($notifications as $notification) {
            $notification->imageURL = User::where('id', $notification->notification_from_user_id)->first()->imageURL;
        }
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
        if($notification_of_user_id == $notification_from_user_id){
            return null;
        }
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
    public function readAll(Request $request)
    {
        $notificationObjects = Notification_Curr::where('notification_of_user_id', $request->user()->id)->get();
        //Set all notifications to read
        foreach ($notificationObjects as $notificationObject) {
            $notificationObject->is_read = true;
            $notificationObject->save();
        }
        $response = [
            'message' => 'All notifications read'
        ];
        return response($response, 200);
    }

    /**
     * Unread notifs count
     */
    public function unreadCount(Request $request)
    {
        $unreadCount = Notification_Curr::where('notification_of_user_id', $request->user()->id)->where('is_read', false)->count();
        $response = [
            'message' => 'Unread notifications count',
            'unreadCount' => $unreadCount
        ];
        return response($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
