<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Billing;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = Client::all();

        foreach($clients as $client){
            for($i = 0; $i < rand(1,5); $i++) {
                $order = Order::factory(['client_id' => $client->id])->create();

                for($j = 0; $j < rand(1,5); $j++) 
                    OrderDetail::factory(['order_id' => $order->id])->create();
                
                Billing::factory(['order_id' => $order->id])->create();
            }
        }
    }
}
