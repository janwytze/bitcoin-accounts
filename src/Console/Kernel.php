<?php
namespace jwz104\Bitcoin\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

use jwz104\Bitcoin\Jobs\LoadTransactionsJob;

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
