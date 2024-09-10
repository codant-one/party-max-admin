<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Order;
use App\Models\Event;

class Service extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'service:create-event';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create events';

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
        self::createEvent();

        return 0;
    }

    private function createEvent() {

        $orders = Order::with(['details.service.categories'])->where('type', 1)->get();

        foreach($orders as $key => $order) {
            foreach($order->details as $key => $detail) {
                $event = new Event;
                $event->category_id = $detail->service->categories[0]->category_id;
                $event->order_detail_id = $detail->id;
                $event->title = $order->reference_code . '-' .$detail->id;
                $event->start_date = $detail->date;
                $event->end_date = $detail->date;
                $event->save();
            }
        }
        
        $this->info('update');
    }
}
