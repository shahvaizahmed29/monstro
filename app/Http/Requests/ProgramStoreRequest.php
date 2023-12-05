<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramStoreRequest extends FormRequest
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
            'location_id' => 'required|integer',
            'custom_field_ghl_id' => 'required',
            'program_name' => 'required|string',
            'description' => 'required|string',
            'capacity' => 'required|integer',
            'min_age' => 'required|integer|min:0',
            'max_age' => 'required|integer|gt:min_age',

            'sessions' => 'required|array',
            'sessions.*.program_level_name' => 'required|string',
            'sessions.*.program_level_ghl_value' => 'required|string',
            'sessions.*.duration_time' => 'required|integer',
            'sessions.*.start_date' => 'required|date',
            'sessions.*.end_date' => 'required|date|after_or_equal:sessions.*.start_date',
            'sessions.*.monday' => 'required|date_format:H:i:s',
            'sessions.*.tuesday' => 'required|date_format:H:i:s',
            'sessions.*.wednesday' => 'required|date_format:H:i:s',
            'sessions.*.thursday' => 'required|date_format:H:i:s',
            'sessions.*.friday' => 'required|date_format:H:i:s',
            'sessions.*.saturday' => 'required|date_format:H:i:s',
            'sessions.*.sunday' => 'required|date_format:H:i:s'
        ];
    }
}
