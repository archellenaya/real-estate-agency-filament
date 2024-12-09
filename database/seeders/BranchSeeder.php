<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Branch::create([
            'id' => 1,
            'name' => 'Branch 1',
            'email' => 'branch@gmail.com',
            'contact_number' =>  fake()->phoneNumber(),
            'address' => fake()->address()
        ]);
    }
}
