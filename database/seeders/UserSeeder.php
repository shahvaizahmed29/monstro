<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Alex',
                'email' => 'alex.gabreil@monto.com',
                'password' => bcrypt('vendor@monsto')
            ],
            [
                'name' => 'John',
                'email' => 'john.safari@monto.com',
                'password' => bcrypt('vendor@monsto')
            ]
        ];
    
        foreach ($users as $user) {
            User::insert($user);
        }
    }
}
