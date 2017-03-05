<?php

namespace Jwz104\BitcoinAccounts\Facades;

use Illuminate\Support\Facades\Facade;

class BitcoinAccounts extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'BitcoinAccounts';
    }
}
