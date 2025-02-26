<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Order;

class DeleteFailedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature =  'delete:failed-orders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete failed orders';

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

        $orders = Order::where('response_code_pol', '23')->get();
            
        foreach($orders as $item){
            $order = Order::find($item->id);
            $order->delete();
        }
    }
}
