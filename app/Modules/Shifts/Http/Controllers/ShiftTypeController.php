<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\ShiftType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShiftTypeController extends Controller
{
    public function index(): View
    {
        $shiftTypes = ShiftType::orderBy('name')->paginate(20);
        return view('modules.shifts.shift-types.index', compact('shiftTypes'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:60|unique:hr_shift_types,name',
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i',
            'color'               => 'nullable|string|max:7',
            'is_night'            => 'boolean',
            'overtime_multiplier' => 'required|numeric|min:1|max:3',
        ]);

        return response()->json(ShiftType::create($data), 201);
    }

    public function update(Request $request, ShiftType $shiftType): JsonResponse
    {
        $data = $request->validate([
            'name'                => 'required|string|max:60|unique:hr_shift_types,name,' . $shiftType->id,
            'start_time'          => 'required|date_format:H:i',
            'end_time'            => 'required|date_format:H:i',
            'color'               => 'nullable|string|max:7',
            'is_night'            => 'boolean',
            'overtime_multiplier' => 'required|numeric|min:1|max:3',
        ]);

        $shiftType->update($data);
        return response()->json($shiftType);
    }

    public function destroy(ShiftType $shiftType): JsonResponse
    {
        $shiftType->delete();
        return response()->json(['ok' => true]);
    }
}
