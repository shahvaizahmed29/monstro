<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // RolesAndPermissionsSeeder::class,
            // PlansSeeder::class,
            // ProgressStepsSeeder::class,
            // ActionSeeder::class,
            // UserSeeder::class,
            // VendorSeeder::class,
            // LocationSeeder::class,
            // MemberSeeder::class,
            // MemberLocationSeeder::class,
            // ProgramSeeder::class,
            // ProgramLevelSeeder::class,
            // SessionSeeder::class,
            // ReservationSeeder::class,
            // CheckInSeeder::class,
            // MemberProgramsSeeder::class
            PermissionsSeeder::class
        ]);
    }
}
