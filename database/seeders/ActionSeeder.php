<?php

namespace Database\Seeders;

use App\Models\Action;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Action::create(['name' => 'No of classes']);
        Action::create(['name' => 'Level acheived']);
        Action::create(['name' => 'No of Referrals']);
    }
}
