<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Http\Requests\StoreShiftRequest;
use App\Modules\Shifts\Http\Requests\UpdateShiftRequest;
use App\Modules\Shifts\Models\Shift;
use App\Modules\Shifts\Services\ShiftService;
use Illuminate\Http\JsonResponse;

class ShiftController extends Controller
{
    public function __construct(private readonly ShiftService $shiftService) {}

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $result = $this->shiftService->createShift($request->validated(), $request->boolean('force'));

        if (isset($result['conflicts'])) {
            return response()->json(['conflicts' => $result['conflicts']], 409);
        }

        return response()->json($result['data'], $result['status']);
    }

    public function update(UpdateShiftRequest $request, Shift $shift): JsonResponse
    {
        $result = $this->shiftService->updateShift($shift, $request->validated(), $request->boolean('force'));

        if (isset($result['conflicts'])) {
            return response()->json(['conflicts' => $result['conflicts']], 409);
        }

        return response()->json($result['data']);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $shift->delete();
        return response()->json(['ok' => true]);
    }
}
