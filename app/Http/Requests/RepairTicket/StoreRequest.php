<?php
// app/Http/Requests/RepairTicket/StoreRequest.php
namespace App\Http\Requests\RepairTicket;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize()
    {
        return true; // or add your authorization logic
    }

    public function rules()
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'product_id' => ['required', 'exists:products,id'],
            'technician_id' => ['nullable', 'exists:users,id'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'problem_description' => ['required', 'string'],
            'photos' => ['nullable', 'array', 'max:3'],
            'photos.*' => ['image', 'max:2048'], // 2MB max per image
        ];
    }
}
