<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VoidCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['manager', 'admin']) ?? false;
    }

    public function rules(): array
    {
        $email = $this->user()?->email;

        return [
            'reason'   => ['required', 'string', 'max:500'],
            'email'    => ['required', 'email', Rule::in(array_filter([$email]))],
            'otp_code' => ['required', 'string', 'min:6', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.in' => __('The confirmation email must match your account email.'),
        ];
    }
}
