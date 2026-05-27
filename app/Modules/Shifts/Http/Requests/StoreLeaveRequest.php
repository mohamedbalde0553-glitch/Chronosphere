<?php

namespace App\Modules\Shifts\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:hr_employees,id',
            'type'        => 'required|string|in:conge_paye,rtt,maladie,sans_solde,autre',
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'reason'      => 'nullable|string',
        ];
    }
}
