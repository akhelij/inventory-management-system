<?php

namespace App\Http\Requests\Customer;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'photo' => [
                'image',
                'file',
                'max:1024'
            ],
            'name' => [
                'required',
                'string',
                'max:50'
            ],
            'type' => [
                'nullable',
                'string',
                'max:25',
                Rule::in(['particulier', 'grossiste', 'magasinier'])
            ],
            'email' => [
                'nullable',
                'email',
                'max:50'
            ],
            'phone' => [
                'nullable',
                'string',
                'max:25'
            ],
            'city' => 'nullable|string|max:25',
            'account_holder' => [
                'max:50'
            ],
            'account_number' => [
                'max:25'
            ],
            'bank_name' => [
                'max:25'
            ],
            'address' => [
                'nullable',
                'string',
                'max:100'
            ],
        ];
    }
}
