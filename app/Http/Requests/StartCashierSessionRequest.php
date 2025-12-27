<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StartCashierSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'beginning_cash' => ['required', 'numeric', 'min:0'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
        ];
    }
}
