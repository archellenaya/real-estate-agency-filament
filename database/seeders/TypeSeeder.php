<?php

namespace Database\Seeders;

use App\Constants\Components\Types;
use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
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
                'name' => Types::BUY,
            ],
            [
                'name' => Types::RENT,
            ],
            [
                'name' => Types::COMMERCIAL,
            ],
        ];

        foreach ($data as $item) {
            $table = new Type;
            $row = $table->where('name', $item['name'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("Type - %s has been added \n", $item['name']);
            } else {
                $row->update($item);
            }
        }
    }
}
