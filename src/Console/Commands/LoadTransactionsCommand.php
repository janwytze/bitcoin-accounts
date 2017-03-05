<?php

namespace Jwz104\BitcoinAccounts\Console\Commands;

use Illuminate\Console\Command;

use Jwz104\BitcoinAccounts\Jobs\LoadTransactionsJob;

class LoadTransactionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bitcoin:loadtransactions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import incomming transactions into the database';

    /**
     * Create a new LoadTransactionsCommand instance.
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
        $this->comment('Starting transaction loader job');
        dispatch(new LoadTransactionsJob());
        $this->comment('Transaction loader job has started');
    }
}
