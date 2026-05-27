<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\DepartmentResource;
use App\Modules\Shifts\Models\Department;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DepartmentApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return DepartmentResource::collection(Department::orderBy('name')->get());
    }
}
