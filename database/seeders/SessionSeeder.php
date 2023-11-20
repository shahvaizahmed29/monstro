<?php

namespace Database\Seeders;

use App\Models\Session;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sessions = [
            [
                'program_level_id' => 1,
                'name' => 'Session 1',
                'duration_time' => 60,
                'start_date' => '2023-11-20',
                'end_date' => '2023-11-20',
                'monday' => '24:37:45',
                'tuesday' => '24:37:45',
                'wednesday' => '24:37:45',
                'thursday' => '24:37:45',
                'friday' => '24:37:45',
                'saturday' => '24:37:45',
                'sunday' => '24:37:45',
                'status' => 1
            ],
            [
                'program_level_id' => 2,
                'name' => 'Session 2',
                'duration_time' => 60,
                'start_date' => '2023-11-21',
                'end_date' => '2023-11-21',
                'monday' => '24:37:45',
                'tuesday' => '24:37:45',
                'wednesday' => '24:37:45',
                'thursday' => '24:37:45',
                'friday' => '24:37:45',
                'saturday' => '24:37:45',
                'sunday' => '24:37:45',
                'status' => 1
            ],
            [
                'program_level_id' => 1,
                'name' => 'Session 3',
                'duration_time' => 60,
                'start_date' => '2023-11-22',
                'end_date' => '2023-11-22',
                'monday' => '24:37:45',
                'tuesday' => '24:37:45',
                'wednesday' => '24:37:45',
                'thursday' => '24:37:45',
                'friday' => '24:37:45',
                'saturday' => '24:37:45',
                'sunday' => '24:37:45',
                'status' => 1
            ],
            [
                'program_level_id' => 1,
                'name' => 'Session 4',
                'duration_time' => 60,
                'start_date' => '2023-11-23',
                'end_date' => '2023-11-23',
                'monday' => '24:37:45',
                'tuesday' => '24:37:45',
                'wednesday' => '24:37:45',
                'thursday' => '24:37:45',
                'friday' => '24:37:45',
                'saturday' => '24:37:45',
                'sunday' => '24:37:45',
                'status' => 1
            ],
        ];

        foreach($sessions as $session){
            Session::insert($session);
        }
        
    }
}
