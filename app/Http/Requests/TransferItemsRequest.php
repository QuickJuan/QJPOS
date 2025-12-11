<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferItemsRequest extends FormRequest
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
            'targetTableId' => 'required|exists:table_rooms,id',
            'cartItemIds'   => ['required', 'array', 'min:1'],
            'cartItemIds.*' => ['integer', 'distinct', 'exists:cart_items,id'],
        ];
    }
}
