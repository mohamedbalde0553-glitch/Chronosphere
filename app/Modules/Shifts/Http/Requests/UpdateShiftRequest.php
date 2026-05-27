<?php

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShiftRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'start_at'      => 'sometimes|required|date',
            'end_at'        => 'sometimes|required|date|after:start_at',
            'employee_id'   => 'sometimes|required|exists:hr_employees,id',
            'shift_type_id' => 'nullable|exists:hr_shift_types,id',
            'status'        => 'nullable|string|in:planned,in_progress,completed,cancelled',
            'notes'         => 'nullable|string',
        ];
    }
}
