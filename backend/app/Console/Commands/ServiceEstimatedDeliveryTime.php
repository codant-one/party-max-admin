<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use Str;

use App\Models\Service;

class ServiceEstimatedDeliveryTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:estimated-delivery-time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Estimated delivery time for services (2 days)';

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
        Service::query()->update(['estimated_delivery_time' => 2]);//2 dias

        $this->info('Services update');

        return 0;
    }
}
