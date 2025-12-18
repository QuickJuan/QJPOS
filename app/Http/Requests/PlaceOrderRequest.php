<?php

namespace App\Http\Requests;

use App\Rules\CartHasUnplacedItems;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
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
              'cart_id' => ['required', 'integer', 'exists:carts,id', new CartHasUnplacedItems()],
              'table_id' => 'required|integer|exists:table_rooms,id',
              'served_by' => 'required|integer|exists:users,id',
        ];
    }
}
