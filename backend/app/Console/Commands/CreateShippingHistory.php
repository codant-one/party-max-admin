<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Str;

use App\Models\ShippingHistory;
use App\Models\Order;

class CreateShippingHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shipping-history:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create shippping histories';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        self::createShippingHistories();

        return 0;
    }

    private function createShippingHistories() {

        $orders = Orders::where('payment_state_id', 4)->get(); 

        foreach($orders as $order){
            ShippingHistory::create([
                'order_id' => $order->id,
                'shipping_state_id' => 1
            ]);
        }
    }
}
