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
            // 'location_id' => 'required|integer',
            'program_name' => 'required|string',
            'description' => 'required|string',
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
            'sessions.*.capacity' => 'required|integer',
            'sessions.*.min_age' => 'required|integer|min:0',
            'sessions.*.max_age' => 'required|integer|gt:sessions.*.min_age',                                                    
            'sessions.*.program_level_name' => 'required|string',
            'sessions.*.duration_time' => 'required|integer',
            // 'sessions.*.start_date' => 'required|date',
            // 'sessions.*.end_date' => 'required|date|after_or_equal:sessions.*.start_date',
            'sessions.*.monday' => 'nullable|date_format:H:i:s',
            'sessions.*.tuesday' => 'nullable|date_format:H:i:s',
            'sessions.*.wednesday' => 'nullable|date_format:H:i:s',
            'sessions.*.thursday' => 'nullable|date_format:H:i:s',
            'sessions.*.friday' => 'nullable|date_format:H:i:s',
            'sessions.*.saturday' => 'nullable|date_format:H:i:s',
            'sessions.*.sunday' => 'nullable|date_format:H:i:s',
            // 'prices' => [
            //     'required',
            //     'array',
            //     function ($attribute, $value, $fail) {
            //         // Collect all prices
            //         $prices = collect($value);
                    
            //         // Group by 'recurring' value
            //         $groupedByRecurring = $prices->groupBy('recurring');
                    
            //         // Iterate through each group and validate
            //         foreach ($groupedByRecurring as $recurring => $items) {
            //             if ($items->count() > 2) {
            //                 // Fail if more than 2 items have the same recurring value
            //                 $fail("The 'recurring' value '$recurring' can only appear in 2 objects.");
                           
            //             } elseif ($items->count() == 2) {
            //                 // Check family condition if there are exactly 2 items with the same recurring value
            //                 $families = $items->pluck('family')->toArray();
            //                 if (count(array_filter($families)) > 1 || count(array_filter($families)) == 0) {
            //                     // Fail if both items have family set to true or both have family set to false
            //                     $fail("When Billing period value is '$recurring', You cannot set both with family or both without family.");
                                
            //                 }
            //             }
            //         }
            //     },
            // ]
        ];
    }
}
