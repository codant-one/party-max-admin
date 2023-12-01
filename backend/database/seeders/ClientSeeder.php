<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\UserDetails;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->count(20)->create();

        $users = User::where("id", ">", 1)
                     ->orderBy('desc')
                     ->limit(20)
                     ->get();

        foreach($users as $user){
            Client::factory(['user_id' => $user->id])->create();
            UserDetails::factory(['user_id' => $user->id])->create();
        }
    }
}
