<?php

namespace App\Http\Requests\RepairTicket;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or add your authorization logic
    }

    public function rules(): array
    {
        return [
            'brought_by' => ['required', 'in:customer,driver'],
            'customer_id' => ['required_if:brought_by,customer', 'nullable', 'exists:customers,id'],
            'driver_id' => ['required_if:brought_by,driver', 'nullable', 'exists:drivers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['required', 'string'],
            'status' => ['in:RECEIVED,IN_PROGRESS,REPAIRED,UNREPAIRABLE,DELIVERED'],
            'status_comment' => ['nullable', 'string', 'max:255'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'max:2048'],
        ];
    }
}
