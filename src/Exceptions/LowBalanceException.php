<?php

namespace Jwz104\BitcoinAccounts\Exceptions;

use Jwz104\BitcoinAccounts\Models\BitcoinUser;

class LowBalanceException extends \Exception {

    /**
     * The user
     *
     * @var Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    protected $user;

    public function __construct(BitcoinUser $user)
    {
        parent::__construct('The user balance is to low');
    }

    /**
     * Get the user who has a to low balance
     *
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function getAccount()
    {
        return $this->user;
    }
}
