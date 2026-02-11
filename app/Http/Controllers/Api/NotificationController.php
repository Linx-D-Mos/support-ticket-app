<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use function Symfony\Component\String\s;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        //Laravel ya relaciona las notificaciones con el usuario automáticamente.
        $notifications = $request->user()
            ->unreadNotifications()
            ->take(10)
            ->get();
        return response()->json($notifications);
    }
    public function markAsRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()
        ->notifications()
        ->where('id', $id)
        ->firstOrFail();

        $notification->markAsRead();
        return  response()->json(['message' => 'Notificación leída']);
    }
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return response()->json(['message' => 'Todas marcadas como leídas']);
    }
}
