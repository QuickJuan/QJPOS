<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

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
     * Handle a failed validation attempt - return JSON for AJAX requests.
     */
    protected function failedValidation(Validator $validator)
    {
        if ($this->expectsJson() || $this->wantsJson()) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422)
            );
        }

        parent::failedValidation($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'beginning_cash'            => 'sometimes|required|numeric|min:0',
            'shift_no' => 'sometimes|required|numeric|exists:cashier_sessions,id',
            'cashier_id'                => 'sometimes|required|exists:users,id',
            'cash_denomination_details' => ['required', 'array'],
            'cash_denomination_details.currencies' => ['present', 'array'],
            'cash_denomination_details.currencies.*.currency_id' => ['required'],
            'cash_denomination_details.currencies.*.amount_in_currency' => ['required', 'numeric', 'min:0'],
            'cash_denomination_details.currencies.*.amount_in_base' => ['required', 'numeric', 'min:0'],
            'cash_denomination_details.gift_check_total' => ['nullable', 'numeric', 'min:0'],
            'cash_denomination'         => 'nullable|numeric|min:0',
            // 'table_id'                  => 'sometimes|nullable|exists:table_rooms,id',
        ];
    }
}
