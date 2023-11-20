<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\CheckIns;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            VendorSeeder::class,
            LocationSeeder::class,
            VendorLocationSeeder::class,
            MemberSeeder::class,
            MemberLocationSeeder::class,
            ProgramSeeder::class,
            ProgramLevelSeeder::class,
            SessionSeeder::class,
            ReservationSeeder::class,
            CheckInsSeeder::class,
            MemberProgramsSeeder::class
        ]);
    }
}
