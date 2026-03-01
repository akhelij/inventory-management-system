<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'photo' => 'image|file|max:1024',
            'category' => 'nullable|string|in:b2b,b2c',
            'name' => 'required|string|max:50',
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:25',
            'city' => 'nullable|string|max:25',
            'limit' => 'integer',
            'account_holder' => 'max:50',
            'account_number' => 'max:25',
            'bank_name' => 'max:25',
            'address' => 'nullable|string|max:100',
        ];

        if ($this->input('category') === 'b2c') {
            $rules['cin'] = 'nullable|string|max:20|unique:customers,cin';
            $rules['date_of_birth'] = 'nullable|date';
            $rules['cin_photo'] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
