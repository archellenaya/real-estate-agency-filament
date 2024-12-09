<?php

namespace Database\Seeders;

use App\Models\BuyerType;
use Illuminate\Database\Seeder;
use App\Constants\Components\BuyerTypes;

class BuyerTypeSeeder extends Seeder
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
                'name' => BuyerTypes::FIRST_TIME_BUYER,
            ], 
            [
                'name' => BuyerTypes::SECOND_TIME_BUYER,
            ],
            [
                'name' => BuyerTypes::INTERESTED_IN_PROPERTY_INVESTMENTS,
            ],
            [
                'name' => BuyerTypes::INTERESTED_IN_A_HOLIDAY_HOME,
            ],
            [
                'name' => BuyerTypes::INTERESTED_IN_RELOCATING_TO_MALTA,
            ],
            [
                'name' => BuyerTypes::INTERESTED_IN_RESIDENCY_OR_CITIZENSHIP,
            ],
            [
                'name' => BuyerTypes::INTERESTED_IN_COMMERCIAL_PROPERTIES,
            ],
        ];

        foreach ($data as $item) {
            $table = new BuyerType;
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
