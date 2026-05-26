<?php

namespace App\Modules\Booking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Booking\Models\ResourceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceCategoryController extends Controller
{
    public function index(): View
    {
        $categories = ResourceCategory::withCount('resources')->orderBy('name')->paginate(20);
        return view('modules.booking.categories.index', compact('categories'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80|unique:booking_resource_categories,name',
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        return response()->json(ResourceCategory::create($data), 201);
    }

    public function update(Request $request, ResourceCategory $category): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80|unique:booking_resource_categories,name,' . $category->id,
            'icon'        => 'nullable|string|max:50',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        $category->update($data);
        return response()->json($category);
    }

    public function destroy(ResourceCategory $category): JsonResponse
    {
        $category->delete();
        return response()->json(['ok' => true]);
    }
}
