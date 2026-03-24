<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');

        $rules = [
            'name' => 'required|string|max:50',
            'type' => ['nullable', 'string', 'max:25', Rule::in(['particulier', 'grossiste', 'magasinier'])],
            'email' => 'nullable|email|max:50',
            'phone' => 'nullable|string|max:25|unique:customers,phone,'.$customer->id,
            'city' => 'nullable|string|max:25',
            'limit' => 'integer',
            'account_holder' => 'max:50',
            'account_number' => 'max:25',
            'bank_name' => 'max:25',
            'address' => 'nullable|string|max:100',
        ];

        if ($this->input('category') === 'b2c' || $customer->category?->value === 'b2c') {
            $rules['cin'] = 'nullable|string|max:20|unique:customers,cin,'.$customer->id;
            $rules['date_of_birth'] = 'nullable|date';
            $rules['cin_photo'] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
