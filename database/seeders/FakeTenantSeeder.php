<?php

namespace Database\Seeders;

use App\PropertyStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FakeTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // php artisan tenants:seed --class=FakeTenantSeeder --tenants={tenant_id}
        $this->call(BranchSeeder::class);
        $this->call(ConsultantSeeder::class);
        $this->call(RegionSeeder::class);
        $this->call(LocalitySeeder::class);
        $this->call(PropertyStatusSeeder::class);
        $this->call(PropertyTypeGroupSeeder::class)
        $this->call(PropertyTypesSeeder::class);
        $this->call(FeatureSeeder::class);
        $this->call(PropertySeeder::class);
        $this->call(FeaturePropertySeeder::class);
    }
}
