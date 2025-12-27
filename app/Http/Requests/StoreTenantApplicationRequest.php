<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantApplicationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'business_name' => 'required|string|max:255|min:2',
            'business_address' => 'required|string|max:500|min:5',
            'owner_name' => 'required|string|max:255|min:2',
            'owner_email' => 'required|email|max:255|unique:central.users,email',
            'owner_phone' => 'required|string|max:20|min:7|regex:/^[+0-9\s\-\(\)]+$/',
            'business_permit_number' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'accept_terms' => 'required|accepted',
            'accept_privacy' => 'required|accepted',
            'accept_promotions' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'business_name.required' => 'Business name is required',
            'business_name.min' => 'Business name must be at least 2 characters',
            'business_name.max' => 'Business name cannot exceed 255 characters',

            'business_address.required' => 'Business address is required',
            'business_address.min' => 'Business address must be at least 5 characters',
            'business_address.max' => 'Business address cannot exceed 500 characters',

            'owner_name.required' => 'Your name is required',
            'owner_name.min' => 'Your name must be at least 2 characters',
            'owner_name.max' => 'Your name cannot exceed 255 characters',

            'owner_email.required' => 'Email address is required',
            'owner_email.email' => 'Please enter a valid email address',
            'owner_email.unique' => 'This email is already registered',

            'owner_phone.required' => 'Phone number is required',
            'owner_phone.min' => 'Phone number must be at least 7 digits',
            'owner_phone.max' => 'Phone number cannot exceed 20 characters',
            'owner_phone.regex' => 'Please enter a valid phone number',

            'logo.image' => 'Logo must be a valid image file',
            'logo.mimes' => 'Logo must be a JPEG, PNG, JPG, GIF, or WebP file',
            'logo.max' => 'Logo cannot exceed 5MB',

            'accept_terms.required' => 'You must accept the Terms of Service',
            'accept_terms.accepted' => 'You must accept the Terms of Service',

            'accept_privacy.required' => 'You must accept the Privacy Policy',
            'accept_privacy.accepted' => 'You must accept the Privacy Policy',
        ];
    }

    /**
     * Get the data to be validated from the request.
     */
    public function validationData(): array
    {
        return $this->all();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert boolean strings to proper boolean values
        $this->merge([
            'accept_terms' => $this->input('accept_terms') === true || $this->input('accept_terms') === '1' || $this->input('accept_terms') === 'true',
            'accept_privacy' => $this->input('accept_privacy') === true || $this->input('accept_privacy') === '1' || $this->input('accept_privacy') === 'true',
            'accept_promotions' => $this->input('accept_promotions') === true || $this->input('accept_promotions') === '1' || $this->input('accept_promotions') === 'true' || false,
        ]);
    }
}
