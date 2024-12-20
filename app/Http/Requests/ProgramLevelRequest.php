<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramLevelRequest extends FormRequest
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
            'name' => 'required|string',
            'program_id' => 'required|integer', 
            'capacity' => 'required|integer',
            'min_age' => 'required|integer|min:0',
            'max_age' => 'required|integer|gt:min_age',
            'sessions' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    // Check if all sessions have at least one day with a value
                    $allHaveDayValues = collect($value)->every(function ($session) {
                        return collect($session)->only(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'])->filter()->isNotEmpty();
                    });
            
                    if (!$allHaveDayValues) {
                        $fail('Every session must have at least one day with a time value.');
                    }
                },
            ],
            // Other validation rules...                       
            'sessions.*.duration_time' => 'required|string',
            'sessions.*.monday' => 'nullable|date_format:H:i:s',
            'sessions.*.tuesday' => 'nullable|date_format:H:i:s',
            'sessions.*.wednesday' => 'nullable|date_format:H:i:s',
            'sessions.*.thursday' => 'nullable|date_format:H:i:s',
            'sessions.*.friday' => 'nullable|date_format:H:i:s',
            'sessions.*.saturday' => 'nullable|date_format:H:i:s',
            'sessions.*.sunday' => 'nullable|date_format:H:i:s',
        ];
    }
}
