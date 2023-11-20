<?php

namespace Database\Seeders;

use App\Models\Reservation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReservationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reservations = [
            [
                'session_id' => 1,
                'member_id' => 1,
                'status' => 1,
                'start_date' => '2023-11-20',
            ],
            [
                'session_id' => 2,
                'member_id' => 1,
                'status' => 1,
                'start_date' => '2023-11-21',
            ],
            [
                'session_id' => 3,
                'member_id' => 1,
                'status' => 1,
                'start_date' => '2023-11-22',
            ],
            [
                'session_id' => 4,
                'member_id' => 2,
                'status' => 1,
                'start_date' => '2023-11-23',
            ],
            [
                'session_id' => 1,
                'member_id' => 2,
                'status' => 1,
                'start_date' => '2023-11-20',
            ],
            [
                'session_id' => 2,
                'member_id' => 2,
                'status' => 1,
                'start_date' => '2023-11-21',
            ],
            [
                'session_id' => 3,
                'member_id' => 2,
                'status' => 1,
                'start_date' => '2023-11-22',
            ],
            [
                'session_id' => 4,
                'member_id' => 2,
                'status' => 1,
                'start_date' => '2023-11-23',
            ],
        ];

        foreach($reservations as $reservation){
            Reservation::insert($reservation);
        }

    }
}
