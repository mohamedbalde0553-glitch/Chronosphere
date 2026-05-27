<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShiftResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'employee_id'      => $this->employee_id,
            'shift_type'       => $this->whenLoaded('shiftType', fn() => [
                'id'    => $this->shiftType->id,
                'name'  => $this->shiftType->name,
                'color' => $this->shiftType->color,
            ]),
            'start_at'         => $this->start_at?->toIso8601String(),
            'end_at'           => $this->end_at?->toIso8601String(),
            'actual_start_at'  => $this->actual_start_at?->toIso8601String(),
            'actual_end_at'    => $this->actual_end_at?->toIso8601String(),
            'worked_minutes'   => $this->worked_minutes,
            'overtime_minutes' => $this->overtime_minutes,
            'break_minutes'    => $this->break_minutes,
            'status'           => $this->status,
            'notes'            => $this->notes,
        ];
    }
}
