<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;
use App\Constants\Components\Interests;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'name' => Interests::NO_PREFERENCE,
            ], 
            [
                'name' => Interests::PROPERTIES_FOR_SALE,
            ],
            [
                'name' => Interests::PROPERTIES_FOR_LET,
            ],
            [
                'name' => Interests::BOTH,
            ],
        ];

        foreach ($data as $item) {
            $table = new Interest;
            $row = $table->where('name', $item['name'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("Buyer - %s has been added \n", $item['name']);
            } else {
                $row->update($item);
            }
        }
    }
}
