<?php

namespace App\Modules\Timetable\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Timetable\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = Teacher::with('user')->orderBy('id')->paginate(20);
        $users    = User::doesntHave('teacher')->orderBy('name')->get(['id', 'name', 'email']);
        return view('modules.timetable.teachers.index', compact('teachers', 'users'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'       => 'required|exists:users,id|unique:uni_teachers,user_id',
            'employee_code' => 'required|string|max:30|unique:uni_teachers,employee_code',
            'title'         => 'nullable|string|max:20',
            'speciality'    => 'nullable|string|max:100',
            'contract_type' => 'nullable|string|in:permanent,contractuel,vacataire',
        ]);

        $data['is_active'] = true;
        $teacher = Teacher::create($data);
        $teacher->load('user');

        // Assign uni_teacher role
        $teacher->user->assignRole('uni_teacher');

        return response()->json($teacher, 201);
    }

    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $data = $request->validate([
            'employee_code' => 'required|string|max:30|unique:uni_teachers,employee_code,' . $teacher->id,
            'title'         => 'nullable|string|max:20',
            'speciality'    => 'nullable|string|max:100',
            'contract_type' => 'nullable|string|in:permanent,contractuel,vacataire',
            'is_active'     => 'boolean',
        ]);

        $teacher->update($data);
        return response()->json($teacher);
    }

    public function destroy(Teacher $teacher): JsonResponse
    {
        $teacher->delete();
        return response()->json(['ok' => true]);
    }
}
