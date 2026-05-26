<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\Resource;
use App\Modules\Booking\Models\ResourceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function index(): View
    {
        $resources  = Resource::with('category')->orderBy('name')->paginate(20);
        $categories = ResourceCategory::orderBy('name')->get(['id', 'name']);

        return view('modules.booking.resources.index', compact('resources', 'categories'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id'                  => 'nullable|exists:booking_resource_categories,id',
            'name'                         => 'required|string|max:100',
            'description'                  => 'nullable|string',
            'capacity'                     => 'required|integer|min:1|max:9999',
            'location'                     => 'nullable|string|max:191',
            'color'                        => 'nullable|string|max:7',
            'is_active'                    => 'boolean',
            'requires_approval'            => 'boolean',
            'advance_booking_days'         => 'required|integer|min:1|max:365',
            'max_booking_duration_minutes' => 'required|integer|min:30|max:14400',
        ]);

        return response()->json(Resource::create($data), 201);
    }

    public function update(Request $request, Resource $resource): JsonResponse
    {
        $data = $request->validate([
            'category_id'                  => 'nullable|exists:booking_resource_categories,id',
            'name'                         => 'required|string|max:100',
            'description'                  => 'nullable|string',
            'capacity'                     => 'required|integer|min:1|max:9999',
            'location'                     => 'nullable|string|max:191',
            'color'                        => 'nullable|string|max:7',
            'is_active'                    => 'boolean',
            'requires_approval'            => 'boolean',
            'advance_booking_days'         => 'required|integer|min:1|max:365',
            'max_booking_duration_minutes' => 'required|integer|min:30|max:14400',
        ]);

        $resource->update($data);
        return response()->json($resource);
    }

    public function destroy(Resource $resource): JsonResponse
    {
        $resource->delete();
        return response()->json(['ok' => true]);
    }
}
