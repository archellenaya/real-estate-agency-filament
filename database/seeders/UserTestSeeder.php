<?php

namespace Database\Seeders;

use App\Constants\Components\UserTypes;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\Profile;
class UserTestSeeder extends Seeder
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
                'name' => 'arch',
                'username' => 'archell',
                'email' => 'archell.enaya@webee.com.mt',
                'password' => bcrypt('Webd1234!'),
                'active' => 1,
                'email_verified_at' => Carbon::now(),
                'user_type_id' => UserType::where("type", UserTypes::PUBLIC_USER)->first()->id
            ] 

        ];


        foreach ($data as $item) {
            $table = new User;
            $row = $table->where('email', $item['email'])->first();
            $email = $item['email'];

      
            if($email == 'archell.enaya@webee.com.mt') {
                $first_name = 'Arch';
                $last_name = 'Enaya';
            }
  

            $user = User::updateOrCreate([
                'email' => $item['email'],
            ], $item);
            
            $addupdateProfile = Profile::updateOrCreate([
                'user_id'   =>  $user->id,
            ],[ 
                'first_name' => $first_name,
                'last_name' => $last_name,
            ]);
            echo sprintf("User - %s has been added \Firstname : %s\n", $user->email, $addupdateProfile->first_name);
        }
    }
}
