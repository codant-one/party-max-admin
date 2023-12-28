<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Address;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();

        foreach($clients as $client){
            for($i = 0; $i < rand(1,3); $i++)
                Address::factory(['client_id' => $client->id])->create();
        }
    }
}
