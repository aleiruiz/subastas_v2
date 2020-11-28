<?php

namespace App\Console;

use App\Jobs\AutomaticAuctionMoneyRelease;
use App\Jobs\CheckAuctionCompletion;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new CheckAuctionCompletion())->dailyAt('00:01');

        if (!settings('seller_money_release', true)) {
            $schedule->job(new AutomaticAuctionMoneyRelease())->dailyAt('00:01');
        }

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
