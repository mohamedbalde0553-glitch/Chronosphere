<?php

namespace App\Modules\Shifts\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shifts\Models\Department;
use App\Modules\Shifts\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::with(['manager.user', 'parent'])->orderBy('name')->paginate(20);
        $employees   = Employee::active()->select('id', 'user_id')->with(['user:id,name'])->orderBy('id')->get();
        $allDepts    = Department::orderBy('name')->get(['id', 'name']);

        return view('modules.shifts.departments.index', compact('departments', 'employees', 'allDepts'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'code'        => 'required|string|max:20|unique:hr_departments,code',
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:hr_employees,id',
            'parent_id'   => 'nullable|exists:hr_departments,id',
        ]);

        return response()->json(Department::create($data), 201);
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:80',
            'code'        => 'required|string|max:20|unique:hr_departments,code,' . $department->id,
            'color'       => 'nullable|string|max:7',
            'description' => 'nullable|string',
            'manager_id'  => 'nullable|exists:hr_employees,id',
            'parent_id'   => 'nullable|exists:hr_departments,id',
        ]);

        $department->update($data);
        return response()->json($department);
    }

    public function destroy(Department $department): JsonResponse
    {
        $department->delete();
        return response()->json(['ok' => true]);
    }
}
