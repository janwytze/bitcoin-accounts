<?php

namespace Jwz104\BitcoinAccounts\Transaction;

use Jwz104\BitcoinAccounts\Transaction\Transaction;
use Jwz104\BitcoinAccounts\Transaction\TransactionLine;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;

class SingleTransaction extends Transaction {

    /**
     * Instantiate a new SingleTransaction instance.
     *
     * @param $bitcoinser Jwz104\BitcoinAccounts\Models\BitcoinUser The bitcoin user
     * @param $address string The destination address
     * @param $amount double The amount of bitcoins
     * @param $fee double The amount of fee, When null or empty use the fee of the config file
     * @return void
     */
    public function __construct(BitcoinUser $bitcoinuser, $address, $amount, $fee = null)
    {
        $transactionline = new TransactionLine($bitcoinuser, $address, $amount, $fee);
        parent::__construct([$transactionline]);
    }
}
