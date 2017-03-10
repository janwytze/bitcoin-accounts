<?php

namespace Jwz104\BitcoinAccounts\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Jwz104\BitcoinAccounts\Facades\BitcoinAccounts;

use Jwz104\BitcoinAccounts\Models\BitcoinHoldTransaction;
use Jwz104\BitcoinAccounts\Models\BitcoinTransaction;

use Jwz104\BitcoinAccounts\Transaction\TransactionLine;
use Jwz104\BitcoinAccounts\Transaction\Transaction;

use Log;

class SendHoldTransactionsJob implements ShouldQueue
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
        $massholdtransactions = BitcoinHoldTransaction::where('type', 'mass')->where('reserved', false)->get();
        $masstransaction = new Transaction();
        foreach ($massholdtransactions as $massholdtransaction) {
            $transactionline = new TransactionLine($massholdtransaction->user, $massholdtransaction->address, $massholdtransaction->amount, $massholdtransaction->fee, $massholdtransaction->id);
            $masstransaction->addLine($transactionline);
            $massholdtransaction->reserved = true;
            $massholdtransaction->save();
        }

        if (isset($transactionline)) {
            try {
                $masstransaction->create();
                $masstransaction->sign();
                $masstransaction->send();

                $this->removeHoldTransactions($massholdtransactions);
            } catch (\Exception $e) {
                $this->revertReserve($massholdtransactions);
                Log::Error($e->getMessage());
            }
        }

        $singleholdtransactions = BitcoinHoldTransaction::where('type', 'single')->where('reserved', false)->get();
        foreach ($singleholdtransactions as $singleholdtransaction) {
            //BitcoinAccounts::sendToAddress($singleholdtransaction->user, $singleholdtransaction->address, $singleholdtransaction->amount, $singleholdtransaction->fee, $singleholdtransaction->id);;
            $singleholdtransaction->delete();
        }
    }

    /**
     * Revert the transactions reserves
     *
     * @param $transaction Jwz104\BitcoinAccounts\Models\BitcoinHoldTransaction The transactions to revert
     * @return void
     */
    protected function revertReserve($transactions)
    {
        foreach ($transactions as $transaction) {
            $transaction->reserved = false;
            $transaction->save();
        }
    }

    /**
     * Remove the hold transactions
     *
     * @param $transaction Jwz104\BitcoinAccounts\Models\BitcoinHoldTransaction The transactions to remove
     * @return void
     */
    protected function removeHoldTransactions($transactions)
    {
        foreach ($transactions as $transaction) {
            $transaction->delete();
        }
    }
}
