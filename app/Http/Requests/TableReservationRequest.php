<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableReservationRequest extends FormRequest
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
            "table_room_id"    => "required|exists:table_rooms,id",
            "reservation_from" => "required|string",
            "reservation_to"   => "required|string",
            "name"             => "required|string|max:255",
            "pax"              => "required|string|max:255",
            "contact_number"   => "nullable|string|max:255",
            "contact_email"    => "nullable|string|max:255",
            "notes"            => "nullable|string|max:1000",
        ];
    }
}
