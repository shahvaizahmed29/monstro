<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramLevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::take(2)->get();

        $levels = [
            [
                'name' => 'Level 1',
                'custom_field_ghl_id' => 'Level 1',
                'parent_id' => null
            ],
            [
                'name' => 'Level 2',
                'custom_field_ghl_id' => 'Level 2',
                'parent_id' => null
            ]
        ];

        foreach ($programs as $index => $program) {
            if (isset($levels[$index])) {
                $level = $levels[$index];

                $program->levels()->create([
                    'name' => $level['name'],
                    'parent_id' => $level['parent_id']
                ]);
            }
        }
    }
}
