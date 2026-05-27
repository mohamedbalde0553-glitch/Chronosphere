<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkScheduleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'description'   => $this->description,
            'start_date'    => $this->start_date?->toDateString(),
            'end_date'      => $this->end_date?->toDateString(),
            'color'         => $this->color,
            'is_active'     => $this->is_active,
            'department'    => $this->whenLoaded('department', fn() => [
                'id'   => $this->department->id,
                'name' => $this->department->name,
            ]),
            'days'          => $this->whenLoaded('days', fn() => $this->days->map(fn($d) => [
                'id'                  => $d->id,
                'day_of_week'         => $d->day_of_week,
                'start_time'          => substr($d->start_time, 0, 5),
                'end_time'            => substr($d->end_time, 0, 5),
                'break_minutes'       => $d->break_minutes,
                'is_overtime_eligible'=> $d->is_overtime_eligible,
                'multiplier'          => $d->multiplier,
                'worked_minutes'      => $d->workedMinutes(),
            ])),
            'created_at'    => $this->created_at?->toIso8601String(),
        ];
    }
}
