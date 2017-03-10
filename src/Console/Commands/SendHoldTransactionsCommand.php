<?php

namespace Jwz104\BitcoinAccounts\Console\Commands;

use Illuminate\Console\Command;

use Jwz104\BitcoinAccounts\Jobs\SendHoldTransactionsJob;

class SendHoldTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitcoin:sendholdtransactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send all the hold transactions from the database';

    /**
     * Create a new SendHoldTransactionsCommand instance.
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
     * @return mixed
     */
    public function handle()
    {
        $this->comment('Starting transaction sender job');
        dispatch(new SendHoldTransactionsJob());
        $this->info('Transaction sender job has started');
    }
}
