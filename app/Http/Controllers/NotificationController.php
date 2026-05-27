<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $notifications = auth()->user()
            ->notifications()
            ->take(20)
            ->get()
            ->map(fn ($n) => [
                'id'        => $n->id,
                'message'   => $n->data['message'] ?? '',
                'url'       => $n->data['url'] ?? null,
                'type'      => $n->data['type'] ?? 'info',
                'read'      => !is_null($n->read_at),
                'created_at'=> $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => auth()->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id) {
            auth()->user()->notifications()->where('id', $id)->update(['read_at' => now()]);
        } else {
            auth()->user()->unreadNotifications->markAsRead();
        }

        return response()->json(['ok' => true]);
    }
}
