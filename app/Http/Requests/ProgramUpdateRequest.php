<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProgramUpdateRequest extends FormRequest
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
            'program_name' => 'required|string',
            'description' => 'required|string',                     
            'sessions.*.capacity' => 'required|integer',
            'sessions.*.min_age' => 'required|integer|min:0',
            'sessions.*.max_age' => 'required|integer|gt:sessions.*.min_age',                                                    
            'sessions.*.program_level_name' => 'required|string',
            'sessions.*.duration_time' => 'required|integer'
        ];
    }
}
