<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Timetable\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::orderBy('name')->paginate(20);
        return view('modules.timetable.subjects.index', compact('subjects'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code'        => 'required|string|max:20|unique:uni_subjects,code',
            'name'        => 'required|string|max:100',
            'coefficient' => 'required|numeric|min:0.5|max:10',
            'ects'        => 'required|integer|min:1|max:30',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        return response()->json(Subject::create($data), 201);
    }

    public function update(Request $request, Subject $subject): JsonResponse
    {
        $data = $request->validate([
            'code'        => 'required|string|max:20|unique:uni_subjects,code,' . $subject->id,
            'name'        => 'required|string|max:100',
            'coefficient' => 'required|numeric|min:0.5|max:10',
            'ects'        => 'required|integer|min:1|max:30',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
        ]);

        $subject->update($data);
        return response()->json($subject);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();
        return response()->json(['ok' => true]);
    }
}
