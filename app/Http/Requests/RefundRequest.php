<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RefundRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Any authenticated user can request a refund
        // The actual authorization is done via OTP verification in the controller
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes'           => ['required', 'string', 'max:500'],
            'supervisor_id'   => ['required', 'integer', 'exists:users,id'],
            'otp_code'        => ['required', 'string', 'min:6', 'max:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'supervisor_id.required' => 'Please select a supervisor for authorization.',
            'supervisor_id.exists' => 'The selected supervisor does not exist.',
            'otp_code.required' => 'OTP code is required.',
        ];
    }
}
