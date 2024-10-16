<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            [
                'name' => 'Program 1',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry`s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'avatar' => null,
            ],
            [
                'name' => 'Program 2',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry`s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'avatar' => null,
            ],
            [
                'name' => 'Program 3',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry`s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'avatar' => null,
            ],
            [
                'name' => 'Program 3',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry`s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
                'avatar' => null,
            ],
        ];

        $locations = Location::take(3)->get();

        foreach ($locations as $index => $location) {
            if (isset($programs[$index])) {
                $program = $programs[$index];
        
                $newProgram = new Program([
                    // 'custom_field_ghl_id' => $program['custom_field_ghl_id'],
                    'name' => $program['name'],
                    'description' => $program['description'],
                    'avatar' => $program['avatar']
                ]);
        
                $location->programs()->save($newProgram);
            }
        }
    }
}
