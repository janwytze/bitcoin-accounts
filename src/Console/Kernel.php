<?php
namespace Jwz104\BitcoinAccounts\Console;

use App\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;

use Jwz104\BitcoinAccounts\Jobs\LoadTransactionsJob;
use Jwz104\BitcoinAccounts\Jobs\SendHoldTransactionsJob;

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
        })->cron(config('bitcoinaccounts.cronjob.load'));

        //send the transactions every 10 minutes
        $schedule->call(function () {
            dispatch(new SendHoldTransactionsJob());
        })->cron(config('bitcoinaccounts.cronjob.send'));
    }
}
