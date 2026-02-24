<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_image' => 'nullable|image|file|max:2048',
            'name' => 'required|string',
            'code' => 'required|string',
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
}
