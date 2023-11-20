<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = [
            [
                'go_high_level_location_id' => 79879879798,
                'name' => 'California Junction',
                'address' => 'Main Square Road California',
                'city' => 'California',
                'state' => 'California',
                'logo_url' => null,
                'country' => 'California',
                'postal_code' => '11215',
                'website' => 'https://www.californiajunction.com',
                'first_name' => 'CA',
                'last_name' => '(USA)',
                'email' => 'california.location@gmail.com',
                'phone' => '+198998768772',
                'meta_data' => [
                    'name' => 'California Junction',
                    'address' => 'Main Square Road California',
                    'go_high_level_location_id' => 79879879798,
                    'city' => 'California',
                    'state' => 'California',
                    'postal_code' => '11215',
                ],
            ],
            [
                'go_high_level_location_id' => 123456789,
                'name' => 'Texas Station',
                'address' => 'Main Street Texas',
                'city' => 'Houston',
                'state' => 'Texas',
                'logo_url' => null,
                'country' => 'USA',
                'postal_code' => '77002',
                'website' => 'https://www.texasstation.com',
                'first_name' => 'TX',
                'last_name' => '(USA)',
                'email' => 'texas.station@gmail.com',
                'phone' => '+12897654321',
                'meta_data' => [
                    'name' => 'Texas Station',
                    'address' => 'Main Street Texas',
                    'go_high_level_location_id' => 123456789,
                    'city' => 'Houston',
                    'state' => 'Texas',
                    'postal_code' => '77002',
                ],
            ],
            [
                'go_high_level_location_id' => 987654321,
                'name' => 'New York Plaza',
                'address' => 'Broadway Avenue',
                'city' => 'New York City',
                'state' => 'New York',
                'logo_url' => null,
                'country' => 'USA',
                'postal_code' => '10001',
                'website' => 'https://www.newyorkplaza.com',
                'first_name' => 'NY',
                'last_name' => '(USA)',
                'email' => 'newyork.plaza@gmail.com',
                'phone' => '+12129876543',
                'meta_data' => [
                    'name' => 'New York Plaza',
                    'address' => 'Broadway Avenue',
                    'go_high_level_location_id' => 987654321,
                    'city' => 'New York City',
                    'state' => 'New York',
                    'postal_code' => '10001',
                ],
            ],
        ];

        foreach ($locations as $location) {
            Location::create([
                'go_high_level_location_id' => $location['go_high_level_location_id'],
                'name' => $location['name'],
                'address' => $location['address'],
                'city' => $location['city'],
                'state' => $location['state'],
                'logo_url' => $location['logo_url'],
                'country' => $location['country'],
                'postal_code' => $location['postal_code'],
                'website' => $location['website'],
                'first_name' => $location['first_name'],
                'last_name' => $location['last_name'],
                'email' => $location['email'],
                'phone' => $location['phone'],
                'meta_data' => json_encode($location['meta_data']),
            ]);
        }
        
    }
}
