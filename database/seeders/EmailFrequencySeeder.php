<?php

namespace Database\Seeders;

use App\Constants\Components\EmailFrequencies;
use App\Models\EmailFrequency;
use Illuminate\Database\Seeder;

class EmailFrequencySeeder extends Seeder
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
                'name' => EmailFrequencies::DAILY,
            ],
            [
                'name' => EmailFrequencies::WEEKLY,
            ],
            [
                'name' => EmailFrequencies::MONTHLY,
            ],
            [
                'name' => EmailFrequencies::YEARLY,
            ],
        ];

        foreach ($data as $item) {
            $table = new EmailFrequency;
            $row = $table->where('name', $item['name'])->first();
            if (empty($row)) {
                $table->create($item);
                echo sprintf("Email Frequency - %s has been added \n", $item['name']);
            } else {
                $row->update($item);
            }
        }
    }
}
