<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Locality;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LocalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 0; $i < 10; $i++) {
            // Fetch a random Region inside the loop
            $region = Region::inRandomOrder()->first();

            Locality::create([
                'old_id' => fake()->uuid(),
                'locality_name' => fake()->city,
                'region' => $region->description,
                'status' => 1
            ]);
        }
    }
}
