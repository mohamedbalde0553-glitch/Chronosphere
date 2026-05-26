<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timetable\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function index(): View
    {
        $rooms = Room::orderBy('code')->paginate(20);
        return view('modules.timetable.rooms.index', compact('rooms'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'       => 'required|string|max:20|unique:uni_rooms,code',
            'name'       => 'required|string|max:100',
            'capacity'   => 'required|integer|min:1|max:500',
            'type'       => 'required|string|in:amphi,td,tp,labo,info,other',
            'building'   => 'nullable|string|max:50',
            'floor'      => 'nullable|string|max:10',
            'is_active'  => 'boolean',
        ]);

        $room = Room::create($data);
        return response()->json($room, 201);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $data = $request->validate([
            'code'      => 'required|string|max:20|unique:uni_rooms,code,' . $room->id,
            'name'      => 'required|string|max:100',
            'capacity'  => 'required|integer|min:1|max:500',
            'type'      => 'required|string|in:amphi,td,tp,labo,info,other',
            'building'  => 'nullable|string|max:50',
            'floor'     => 'nullable|string|max:10',
            'is_active' => 'boolean',
        ]);

        $room->update($data);
        return response()->json($room);
    }

    public function destroy(Room $room): JsonResponse
    {
        $room->delete();
        return response()->json(['ok' => true]);
    }
}
