<?php

namespace Jwz104\BitcoinAccounts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Jwz104\BitcoinAccounts\Facades\BitcoinAccounts;

use Jwz104\BitcoinAccounts\Models\BitcoinTransaction;
use Jwz104\BitcoinAccounts\Models\BitcoinAddress;

class LoadTransactionsJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $page = 0;
        while (true) {
            $transactions = $this->getTransactions($page);
            $changed = $this->handleTransactions($transactions);
            if (!$changed) {
                break;
            }
            $page++;
        }
    }

    /**
     * Get all the transactions and filter them.
     *
     * @param $page int The page to get, 50 per page, starts at 0
     * @return array
     */
    protected function getTransactions($page)
    {
        $transactions = BitcoinAccounts::listTransactions($page*50, 50);
        return $transactions->where('category', 'receive');
    }

    /**
     * Handle all the transactions and return true if something was handled
     *
     * @param $transactions array
     * @return boolean
     */
    protected function handleTransactions($transactions)
    {
        $changed = false;
        foreach ($transactions as $transaction) {
            $duplicate = BitcoinTransaction::join('bitcoin_addresses', 'bitcoin_addresses.id', '=', 'bitcoin_transactions.bitcoin_address_id')
                ->where('bitcoin_transactions.txid', $transaction['txid'])
                ->where('bitcoin_addresses.address', $transaction['address'])
                ->first();
            if ($duplicate == null) {
                //Check if the transaction address is registered, and if it belongs to an user
                $bitcoinaddress = BitcoinAddress::where('address', $transaction['address'])->whereNotNull('bitcoin_user_id')->first();
                if ($bitcoinaddress != null) {
                    $bitcointransaction = new BitcoinTransaction();

                    $bitcointransaction->bitcoin_user_id = $bitcoinaddress->bitcoin_user_id;
                    $bitcointransaction->bitcoin_address_id = $bitcoinaddress->id;
                    $bitcointransaction->txid = $transaction['txid'];
                    $bitcointransaction->amount = $transaction['amount'];
                    $bitcointransaction->type = 'receive';

                    $bitcointransaction->save();
                    $changed = true;
                }
            }
        }
        return $changed;
    }
}
