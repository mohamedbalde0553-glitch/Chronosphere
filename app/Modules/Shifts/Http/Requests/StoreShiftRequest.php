<?php

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id'   => 'required|exists:hr_employees,id',
            'shift_type_id' => 'nullable|exists:hr_shift_types,id',
            'start_at'      => 'required|date',
            'end_at'        => 'required|date|after:start_at',
            'notes'         => 'nullable|string',
        ];
    }
}
