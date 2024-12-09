<?php

namespace Database\Seeders;

use App\Constants\Components\UniqueLinkTypes;
use App\Models\UniqueLinkType;
use Illuminate\Database\Seeder;

class UniqueLinkTypeSeeder extends Seeder
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
                'type' => UniqueLinkTypes::USER_REGISTRATION,
            ],
            [
                'type' => UniqueLinkTypes::RESET_PASSWORD,
            ],
        ];

        foreach ($data as $item) {
            $table = new UniqueLinkType;
            $row = $table->where('type', $item['type'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("One Time Link Type - %s has been added \n", $item['type']);
            } else {
                $row->update($item);
            }
        }
    }
}
