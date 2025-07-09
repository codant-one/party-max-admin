<?php

namespace Database\Seeders;

use App\Models\OldUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class OldUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $json_info = Storage::disk('local')->get('/json/old_users.json');
        $old_users = json_decode($json_info, true);

        foreach($old_users as $user){
            OldUser::query()->updateOrCreate([
                'first_name' => ucwords(strtolower($user['first_name'])),
                'last_name' => ucwords(strtolower($user['last_name'])),
                'email' => strtolower($user['email']),
                'city' => $user['city'],
                'state' => $user['state'],
            ]);
        }
    }
}
