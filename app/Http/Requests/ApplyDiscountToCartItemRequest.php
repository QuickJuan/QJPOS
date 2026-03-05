<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyDiscountToCartItemRequest extends FormRequest
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
            'cartItemIds'   => ['required', 'array', 'min:1'],
            'cartItemIds.*' => ['integer', 'distinct', 'exists:cart_items,id'],
            'discount_id'   => 'required|numeric|exists:discounts,id',
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
