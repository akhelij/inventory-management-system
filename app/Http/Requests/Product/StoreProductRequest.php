<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
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
            'product_image' => 'image|file|max:2048',
            'name' => 'required|string',
            'code' => 'required|string|unique:products,code',
            'category_id' => 'nullable|integer|exists:categories,id',
            'warehouse_id' => 'required|integer|exists:warehouses,id',
            'unit_id' => 'required|integer',
            'quantity' => 'required|integer',
            'buying_price' => 'required|integer',
            'selling_price' => 'required|integer',
            'quantity_alert' => 'required|integer',
            'tax' => 'nullable|numeric',
            'tax_type' => 'nullable|integer',
            'notes' => 'nullable|max:1000',
        ];
    }

    // protected function prepareForValidation(): void
    // {
    //     $this->merge([
    //         'slug' => Str::slug($this->name, '-'),
    //         'code' =>
    //     ]);
    // }
}
