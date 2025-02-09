<?php

namespace App\Http\Requests\RepairTicket;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['required', 'string'],
            'photos.*' => ['nullable', 'image', 'max:2048'], // 2MB max
            'photos' => ['array', 'max:3'] // max 3 photos
        ];
    }
}
