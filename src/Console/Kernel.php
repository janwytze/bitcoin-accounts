<?php
namespace Jwz104\BitcoinAccounts\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

use Jwz104\BitcoinAccounts\Jobs\LoadTransactionsJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the package's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        parent::schedule($schedule);

        //Load the transactions every minute
        $schedule->call(function () {
            dispatch(new LoadTransactionsJob());
        })->everyMinute();
    }
}
