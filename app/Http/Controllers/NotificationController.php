<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch the authenticated user's notifications.
     */
    public function index(Request $request)
    {
        // Get the 50 most recent notifications for the logged-in user.
        $notifications = $request->user()->notifications()->latest()->limit(50)->get();

        return response()->json([
            'data' => $notifications,
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, $notificationId)
    {
        $notification = $request->user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        return response()->noContent();
    }
}
