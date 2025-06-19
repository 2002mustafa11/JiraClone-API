<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'notifications' => $user->notifications,  // جميع الإشعارات
        ]);
    }

    public function unread()
    {
        $user = Auth::user();
        return response()->json([
            'status' => true,
            'unread_notifications' => $user->unreadNotifications, 
        ]);
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json([
            'status' => true,
            'message' => 'تم وضع علامة مقروءة على جميع الإشعارات',
        ]);
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->where('id', $id)->first();

        if ($notification) {
            $notification->markAsRead();
            return response()->json([
                'status' => true,
                'message' => 'تم وسم الإشعار كمقروء',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'الإشعار غير موجود',
        ], 404);
    }
}
