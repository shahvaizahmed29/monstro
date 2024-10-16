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
                'parent_id' => null,
                'capacity' => 18,
                'min_age' => 16,
                'max_age' => 48,
            ],
            [
                'name' => 'Level 2',
                'parent_id' => null,
                'capacity' => 18,
                'min_age' => 16,
                'max_age' => 48,
            ]
        ];

        foreach ($programs as $index => $program) {
            if (isset($levels[$index])) {
                $level = $levels[$index];

                $program->programLevels()->create([
                    'name' => $level['name'],
                    'parent_id' => $level['parent_id'],
                    'capacity' => $level['capacity'],
                    'min_age' => $level['min_age'],
                    'max_age' => $level['max_age'],
                ]);
            }
        }
    }
}
