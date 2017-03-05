<?php

namespace Jwz104\BitcoinAccounts\Transaction;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;
use Jwz104\BitcoinAccounts\Models\BitcoinAddress;
use Jwz104\BitcoinAccounts\Models\BitcoinTransaction;

use Jwz104\BitcoinAccounts\Facades\BitcoinAccounts;

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
     * The amount of fee
     *
     * @var double
     */
    protected $fee;

    /**
     * The bitcoin user
     *
     * @var Jwz104\BitcoinAccounts\Models\BitcoinUser
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
     * @param $bitcoinser Jwz104\BitcoinAccounts\Models\BitcoinUser The bitcoin user
     * @param $address string The destination address
     * @param $amount double The amount of bitcoins
     * @param $fee double The amount of fee
     * @return void
     */
    public function __construct(BitcoinUser $bitcoinuser, $address, $amount, $fee = null)
    {
        if ($fee == null) {
            $fee = config('bitcoinaccounts.bitcoin.transaction-fee');
        }
        $this->bitcoinuser = $bitcoinuser;
        $this->address = $address;
        $this->amount = $amount;
        $this->fee = $fee;
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
        $amount = ($this->amount+$this->fee);
        //Get the required amount of txout to create the transaction
        foreach ($unspent as $transaction) {
            $txout[] = [
                'txid' => $transaction->txid, 
                'vout' => $transaction->vout, 
            ];
            $amount-=$transaction['amount'];
            //Keep going untill there are enough bitcoins
            if ($amount <= 0) {
                break;
            }
        }

        //Check if there is enough balance in unspent
        if ($amount > 0) {
            throw new \Exception('Not enough balance in unspent');
        }

        //Create the raw transaction
        $rawtx = BitcoinAccounts::createRawTransaction($txout, [$this->address => $this->amount]);

        return ($this->rawtx = $rawtw);
    }

    /**
     * Decode raw transaction
     *
     * @return mixed[]
     */
    public function decode()
    {
        if ($this->rawtx == null) {
            return null;
        }
    }
}
