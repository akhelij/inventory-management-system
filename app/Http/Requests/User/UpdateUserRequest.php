<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|max:50',
            'photo' => 'image|file|max:1024',
            'email' => ['required', 'email', 'max:50', Rule::unique('users', 'email')->ignore($this->user)],
            'role_id' => 'exists:roles,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
        ];
    }
}
