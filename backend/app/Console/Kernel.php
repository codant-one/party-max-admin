<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('blogs:update')
                 ->daily()
                 ->at('00:00');

        $schedule->command('order-payment-state:update')
                 ->daily()
                 ->at('00:00');

        $schedule->command('delete:failed-orders')
                 ->daily()
                 ->at('00:00');   
                 
        $schedule->command('orders:send-survey')
                 ->daily()
                 ->at('00:00'); 
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
