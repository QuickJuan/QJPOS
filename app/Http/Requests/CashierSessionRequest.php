<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CashierSessionRequest extends FormRequest
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
            'beginning_cash'            => 'sometimes|required|numeric|min:0',
            'cash_denomination_details' => 'sometimes|required|array',
            'closing_cash'              => 'sometimes|required|numeric|min:0',
            'table_id'                  => 'sometimes|nullable|exists:table_rooms,id',
        ];
    }
}
