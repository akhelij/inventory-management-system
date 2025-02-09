<?php

namespace App\Http\Requests\RepairTicket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function rules()
    {
        return [
            'status' => ['required', 'in:RECEIVED,IN_PROGRESS,REPAIRED,UNREPAIRABLE,DELIVERED'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'status_comment' => ['nullable', 'string', 'max:255']
        ];
    }
}
