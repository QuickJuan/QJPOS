<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'parent_id'            => 'nullable|exists:cart_items,id',
            'product_id'           => 'required|exists:products,id',
            'product_packaging_id' => 'nullable|exists:product_packagings,id',
            'selected_options'     => 'nullable|array',
            'quantity'             => 'nullable|numeric|min:0.01',
            'total_price'          => 'required|numeric|min:0',
            'table_id'             => 'nullable|exists:table_rooms,id',
            'order_type'           => 'nullable|string|in:dine-in,takeout,delivery',
        ];
    }
}
