<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;

class LowUnspentException extends \Exception {

    public function __construct()
    {
        parent::__construct('There are not enough unspent transactions to make this transaction');
    }
}
