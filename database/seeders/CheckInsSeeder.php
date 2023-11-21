<?php

namespace Database\Seeders;

use App\Models\CheckIns;
use Illuminate\Database\Seeder;

class CheckInsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $check_ins = [
            [
                'reservation_id' => 1,
                'check_in_time' => '01:30:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 2,
                'check_in_time' => '01:35:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 3,
                'check_in_time' => '01:40:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 4,
                'check_in_time' => '01:45:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 5,
                'check_in_time' => '01:30:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 6,
                'check_in_time' => '01:35:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 7,
                'check_in_time' => '01:40:00',
                'check_out_time' => '02:00:00'
            ],
            [
                'reservation_id' => 8,
                'check_in_time' => '01:45:00',
                'check_out_time' => '02:00:00'
            ],
        ];

        foreach($check_ins as $check_in){
            CheckIns::insert($check_in);
        }

    }
}
