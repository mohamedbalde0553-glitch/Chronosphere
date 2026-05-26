<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Skill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SkillController extends Controller
{
    public function index(): View
    {
        $skills = Skill::withCount('employees')->orderBy('category')->orderBy('name')->get();
        return view('modules.shifts.skills.index', compact('skills'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100|unique:hr_skills,name',
            'category' => 'nullable|string|max:50',
        ]);
        return response()->json(Skill::create($data), 201);
    }

    public function update(Request $request, Skill $skill): JsonResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100|unique:hr_skills,name,' . $skill->id,
            'category' => 'nullable|string|max:50',
        ]);
        $skill->update($data);
        return response()->json($skill);
    }

    public function destroy(Skill $skill): JsonResponse
    {
        $skill->delete();
        return response()->json(['ok' => true]);
    }
}
