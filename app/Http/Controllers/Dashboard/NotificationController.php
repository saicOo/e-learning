<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\BaseController as BaseController;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * @OA\Get(
     *     path="/api/dashboard/notifications",
     *      tags={"Dashboard Api Notifications"},
     *     summary="Get All Notifications",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function index()
    {
        $user = auth()->user();
        $unreadNotifications = $user->unreadNotifications;
        $unreadNotificationsCount = $user->unreadNotifications->count();
        $readNotifications = $user->readNotifications;
        return $this->sendResponse("",['unread_notifications_count' => $unreadNotificationsCount,
        'unread_notifications' => $unreadNotifications,'read_notifications' => $readNotifications]);
    }

    /**
     * @OA\Post(
     *     path="/api/dashboard/notifications",
     *      tags={"Dashboard Api Notifications"},
     *     summary="Read All Notifications",
     *       @OA\Response(response=200, description="OK"),
     *    )
     */
    public function markAsRead(){
        auth()->user()->unreadNotifications->markAsRead();
        return $this->sendResponse("All notifications have been read successfully");
    }
}
