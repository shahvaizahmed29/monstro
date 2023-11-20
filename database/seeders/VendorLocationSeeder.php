<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use DB;

class VendorLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vendors = Vendor::take(3)->get();
        $locations = Location::take(3)->get();

        foreach ($vendors as $vendor) {
            foreach ($locations as $location) {
                DB::table('vendor_locations')->insert([
                    'vendor_id' => $vendor->id,
                    'location_id' => $location->id
                ]);
            }
        }
    }
}
