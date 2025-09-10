<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use Str;

use App\Models\Order;
use App\Models\Event;

class updateOrderPaymentState extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order-payment-state:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update orders state';

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
        self::updateOrders();

        return 0;
    }

    private function updateOrders() {

        $ordersPending = Order::where('payment_state_id', 1)->get();
        
        foreach ($ordersPending as $item) {

            $order = Order::with(['details'])->find($item->id);

            if($order) {
                foreach($order->details as $detail) {
                    $event = Event::where('order_detail_id', $detail->id)->first();
                    if($event) {
                        $event->state_id = 6;
                        $event->save();
                    }
                }

                $order->update([
                    'payment_state_id' => 2
                ]);  
            }
        }
    }


}
