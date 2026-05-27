<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PositionResource;
use App\Modules\Shifts\Models\Position;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PositionApiController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return PositionResource::collection(Position::orderBy('title')->get());
    }
}
