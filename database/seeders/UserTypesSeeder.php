<?php

namespace Database\Seeders;

use App\Constants\Components\UserTypes;
use App\Models\UserType;
use Illuminate\Database\Seeder;

class UserTypesSeeder extends Seeder
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
                'type' => UserTypes::BACKEND_USER,
            ],
            [
                'type' => UserTypes::PUBLIC_USER,
            ],
        ];

        foreach ($data as $item) {
            $table = new UserType;
            $row = $table->where('type', $item['type'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("User Type - %s has been added \n", $item['type']);
            } else {
                $row->update($item);
            }
        }
    }
}
