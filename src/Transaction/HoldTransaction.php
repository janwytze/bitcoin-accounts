<?php

namespace Jwz104\BitcoinAccounts\Transaction;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;
use Jwz104\BitcoinAccounts\Models\BitcoinHoldTransaction;

use Jwz104\BitcoinAccounts\Exceptions\InvalidTransactionException;
use Jwz104\BitcoinAccounts\Exceptions\LowBalanceException;

use Jwz104\BitcoinAccounts\Facades\BitcoinAccounts;

class HoldTransaction {

    /**
     * The user
     *
     * @var Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    protected $user;

    /**
     * The transaction
     *
     * @var Jwz104\BitcoinAccounts\Models\BitcoinHoldTransaction
     */
    protected $transaction;

    /**
     * Instantiate a new Transaction instance.
     *
     * @param $user Jwz104\BitcoinAccounts\Models\BitcoinUser From user
     * @param $type string The transaction type, "mass" or "single"
     * @param $address Jwz104\BitcoinAccounts\Models\BitcoinAddress To address
     * @param $amount double The amount of bitcoins
     * @param $fee double The amount of fee, leave empty for default amount
     * @return void
     */
    public function __construct(BitcoinUser $user, $type, $address, $amount, $fee = null)
    {
        if ($fee == null) {
            $fee = config('bitcoinaccounts.bitcoin.transaction-fee');
        }

        $this->user = $user;

        $transaction = new BitcoinHoldTransaction();

        $transaction->bitcoin_user_id = $this->user->id;
        $transaction->type = $type;
        $transaction->address = $address;
        $transaction->amount = $amount;
        $transaction->fee = $fee;

        $this->transaction = $transaction;

        $this->check();
    }

    /**
     * Is the transaction saved
     *
     * @return boolean
     */
    public function isSend()
    {
        dd($transaction);
    }

    /**
     * Check if the transaction valid
     *
     * @return void
     */
    public function check()
    {
        if ($this->transaction->amount <= 0 && $this->transaction->fee <= 0) {
            throw new InvalidTransactionException();
        }
        if ($this->transaction->amount < 0 || $this->transaction->fee < 0) {
            throw new InvalidTransactionException();
        }

        if ($this->user->balance() < ($this->transaction->amount+$this->transaction->fee)) {
            throw new LowBalanceException($this->user);
        }
    }

    /**
     * Save the transaction to the datbase
     *
     * @return string
     */
    public function send()
    {
        $this->transaction->save();
    }
}
