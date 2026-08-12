<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function getUnread()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['notifications' => []]);
        }

        $notifications = $user->unreadNotifications;

        return response()->json([
            'notifications' => $notifications
        ]);
    }

    public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
        }

        return redirect()->back();
    }
}
