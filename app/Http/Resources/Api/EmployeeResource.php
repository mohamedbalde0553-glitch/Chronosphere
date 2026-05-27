<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'employee_code'        => $this->employee_code,
            'hire_date'            => $this->hire_date?->toDateString(),
            'contract_type'        => $this->contract_type,
            'status'               => $this->status,
            'photo_url'            => $this->photo_url,
            'weekly_hours_minutes' => $this->weekly_hours_minutes,
            'max_daily_minutes'    => $this->max_daily_minutes,
            'min_rest_minutes'     => $this->min_rest_minutes,
            'user'                 => $this->whenLoaded('user', fn() => [
                'id'     => $this->user->id,
                'name'   => $this->user->name,
                'email'  => $this->user->email,
                'phone'  => $this->user->phone,
                'avatar' => $this->user->avatar,
            ]),
            'department'           => $this->whenLoaded('department', fn() => new DepartmentResource($this->department)),
            'position'             => $this->whenLoaded('position', fn() => new PositionResource($this->position)),
            'skills'               => $this->whenLoaded('skills', fn() => SkillResource::collection($this->skills)),
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }
}
