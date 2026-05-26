<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timetable\Models\AcademicYear;
use App\Modules\Timetable\Models\ClassGroup;
use App\Modules\Timetable\Models\Level;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassGroupController extends Controller
{
    public function index(): View
    {
        $groups       = ClassGroup::with(['level.faculty', 'academicYear'])->orderBy('name')->paginate(20);
        $levels       = Level::with('faculty')->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('start_date')->get();
        return view('modules.timetable.groups.index', compact('groups', 'levels', 'academicYears'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:80',
            'code'             => 'required|string|max:20',
            'level_id'         => 'required|exists:uni_levels,id',
            'academic_year_id' => 'required|exists:uni_academic_years,id',
            'capacity'         => 'required|integer|min:1|max:200',
            'parent_id'        => 'nullable|exists:uni_class_groups,id',
        ]);

        return response()->json(ClassGroup::create($data), 201);
    }

    public function update(Request $request, ClassGroup $group): JsonResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:80',
            'code'             => 'required|string|max:20',
            'level_id'         => 'required|exists:uni_levels,id',
            'academic_year_id' => 'required|exists:uni_academic_years,id',
            'capacity'         => 'required|integer|min:1|max:200',
        ]);

        $group->update($data);
        return response()->json($group);
    }

    public function destroy(ClassGroup $group): JsonResponse
    {
        $group->delete();
        return response()->json(['ok' => true]);
    }
}
