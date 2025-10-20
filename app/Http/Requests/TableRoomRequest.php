<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TableRoomRequest extends FormRequest
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
            'name'                   => 'required|string|max:255',
            'chairs'                 => 'required|integer|min:1|max:100',
            'table_room_location_id' => 'required|exists:table_room_locations,id',
            'table_width'            => 'nullable|integer|min:0|max:1000',
            'table_height'           => 'nullable|integer|min:0|max:1000',
            'table_x'                => 'nullable|integer',
            'table_y'                => 'nullable|integer',
        ];
    }
}
