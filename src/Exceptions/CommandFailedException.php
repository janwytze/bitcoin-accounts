<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

class CommandFailedException extends \Exception {

    public function __toString()
    {
        __toString('Could not execute the command'); 
    }

}
