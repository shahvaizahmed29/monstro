<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = [
            [
                'user_id' => 1,
                'company_name' => 'Company One',
                'company_email' => 'companyone@monto.com',
                'company_website' => 'companyone.com',
                'company_address' => 'South Wales south plot 1-2DA',
                'first_name' => "Vendor",
                'last_name' => "One",

            ],
            [
                'user_id' => 2,
                'company_name' => 'Company Two',
                'company_email' => 'companytwo@monto.com',
                'company_website' => 'companytwo.com',
                'company_address' => 'North Wales south plot 1-2DA',
                'first_name' => "Vendor",
                'last_name' => "Two",
                
            ],
            [
                'user_id' => 3,
                'company_name' => 'Company Three',
                'company_email' => 'companythree@monto.com',
                'company_website' => 'companythree.com',
                'company_address' => 'East Wales south plot 1-2DA',
                'first_name' => "Vendor",
                'last_name' => "Three",
                
            ]
        ];

        foreach ($vendors as $vendor) {
            Vendor::insert($vendor);
        }
    }
}
