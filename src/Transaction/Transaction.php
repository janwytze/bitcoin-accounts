<?php

namespace jwz104\Bitcoin\Transaction;

use jwz104\Bitcoin\Models\BitcoinUser;
use jwz104\Bitcoin\Models\BitcoinAddress;
use jwz104\Bitcoin\Models\BitcoinTransaction;

use BitcoinAccounts;

class Transaction {

    /**
     * The destination address
     *
     * @var string
     */
    protected $address;

    /**
     * The amount of bitcoins
     *
     * @var double
     */
    protected $amount;

    /**
     * The bitcoin user
     *
     * @var jwz104\Bitcoin\Models\BitcoinUser
     */
    protected $bitcoinuser;

    /**
     * The raw transaction id
     *
     * @var sring
     */
    protected $rawtx;

    /**
     * Instantiate a new Transaction instance.
     *
     * @param $bitcoinser jwz104\Bitcoin\Models\BitcoinUser The bitcoin user
     * @param $address string The destination address
     * @param $amount double The amount of bitcoins
     * @return void
     */
    public function __construct(BitcoinUser $bitcoinuser, $address, $amount)
    {
        $this->bitcoinuser = $bitcoinuser;
        $this->address = $address;
        $this->amount = $amount;
    }

    /**
     * Create the transaction and return the raw transaction
     *
     * @return string
     */
    public function createTransaction()
    {
        if ($this->bitcoinuser->balance() < $this->amount) {
            return null;
        }
        //Get all the unspent transactions
        $unspent = collect(BitcoinTransactions::listUnspent())
            ->where('spendable', true)
            ->sortByDesc('amount');

        $txout = [];
        $amount = $this->amount;
        //Get the required amount of txout to create the transaction
        foreach ($unspent as $transaction) {
            $txout[] = [
                'txid' => $transaction->txid, 
                'vout' => $transaction->vout, 
            ];
            $amount-=$transaction['amount'];
            //Keep going untill there are enough bitcoins
            if ($amount < 0) {
                break;
            }
        }

        //Create the raw transaction
        $rawtx = BitcoinTransactions::createRawTransaction($txout, [$this->address => $this->amount]);

        return $this->rawtx = $rawtw;
    }
}
