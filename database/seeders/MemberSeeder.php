<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::orderBy('created_at', 'desc')->take(3)->get();

        $members = [
            [
                'name' => 'Jackson',
                'email' => 'jackson@monto.com',
                'phone' => '+1789276565',
                'referral_code' => '88765',
            ],
            // [
            //     'name' => 'cypher',
            //     'email' => 'cypher@monto.com',
            //     'phone' => '+1789276885',
            //     'referral_code' => '88764',
            // ],
            // [
            //     'name' => 'emma',
            //     'email' => 'emma@monto.com',
            //     'phone' => '+1756276885',
            //     'referral_code' => '88763',
            // ],
        ];

        foreach ($users as $index => $user) {
            if (isset($members[$index])) {
                $member = $members[$index];
                
                $user->member()->create([
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'phone' => $member['phone'],
                    'referral_code' => $member['referral_code'],
                ]);
            }
        }

    }
}
