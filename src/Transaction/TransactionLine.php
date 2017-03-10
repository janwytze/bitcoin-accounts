<?php

namespace Jwz104\BitcoinAccounts\Transaction;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;

use Jwz104\BitcoinAccounts\Exceptions\LowBalanceException;
use Jwz104\BitcoinAccounts\Exceptions\LowUnspentException;
use Jwz104\BitcoinAccounts\Exceptions\InvalidTransactionException;

class TransactionLine {

    /**
     * The Bitcoin user
     *
     * @var Jwz104\BitcoinAccounts\Models\BitcoinUser;
     */
    public $bitcoinuser;

    /**
     * The destination address
     *
     * @var string
     */
    public $address;

    /**
     * The amount of bitcoins to transfer
     *
     * @var double
     */
    public $amount;

    /**
     * The amount of fee to send
     *
     * @var double
     */
    public $fee;

    /**
     * Instantiate a new Transaction instance.
     *
     * @param $bitcoinuser Jwz104\BitcoinAccounts\Models\BitcoinUser The from user;
     * @param $address string The to address;
     * @param $amount double The amount of bitcoins to send;
     * @param $fee double The amount of fee to send;
     * @return void
     */
    public function __construct(BitcoinUser $bitcoinuser, $address, $amount, $fee)
    {
        if ($fee == null) {
            $fee = config('bitcoinaccounts.bitcoin.transaction-fee');
        }

        $this->bitcoinuser = $bitcoinuser;
        $this->address = $address;
        $this->amount = $amount;
        $this->fee = $fee;

        $this->check();
    }

    /**
     * Check if the transaction line is valid and if the balance is high enough
     *
     * @throws Jwz104\BitcoinAccounts\Exceptions\LowBalanceException Thrown when balance is to low
     * @throws Jwz104\BitcoinAccounts\Exceptions\InvalidTransactionException Thrown when transaction is invalid
     * @return void
     */
    public function check()
    {
        //If the transation doesn't contain any bitcoins throw exception
        if ($this->amount <= 0 && $this->fee <= 0) {
            throw new InvalidTransactionException();
        }
        if ($this->amount < 0 || $this->fee < 0) {
            throw new InvalidTransactionException();
        }

        //Throw low balance exception when balance is to low
        if ($this->bitcoinuser->balance() < ($this->amount+$this->fee)) {
            throw new LowBalanceException($this->bitcoinuser);
        }
    }
}
