<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

class LowBalanceException extends \Exception {

    public function __toString()
    {
        __toString('The user balance is to low'); 
    }

}
