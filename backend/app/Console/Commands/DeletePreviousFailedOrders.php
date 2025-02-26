<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Order;

class DeletePreviousFailedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:previous-failed-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete previous failed orders';

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
        self::deleteOrders();

        return 0;
    }

    private function deleteOrders() {

        $orders = Order::where('total', '13000.00')
               ->whereIn('payment_state_id', [2, 3])
               ->get();
        
        foreach($orders as $item){
            $order = Order::find($item->id);
            $order->delete();
        }
    }
}
