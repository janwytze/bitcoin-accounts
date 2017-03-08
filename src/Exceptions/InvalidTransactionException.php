<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;

class InvalidTransactionException extends \Exception {

    public function __construct()
    {
        parent::__construct('The transaction is invalid');
    }
}
