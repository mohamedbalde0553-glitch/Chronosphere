<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LeaveRequestResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'employee_id'      => $this->employee_id,
            'type'             => $this->type,
            'start_date'       => $this->start_date?->toDateString(),
            'end_date'         => $this->end_date?->toDateString(),
            'start_half_day'   => $this->start_half_day,
            'end_half_day'     => $this->end_half_day,
            'reason'           => $this->reason,
            'status'           => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'validated_at'     => $this->validated_at?->toIso8601String(),
            'validator'        => $this->whenLoaded('validator', fn() => [
                'id'   => $this->validator->id,
                'name' => $this->validator->name,
            ]),
            'employee'         => $this->whenLoaded('employee', fn() => [
                'id'   => $this->employee->id,
                'code' => $this->employee->employee_code,
                'name' => $this->employee->user?->name,
            ]),
        ];
    }
}
