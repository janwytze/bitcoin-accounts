<?php

namespace jwz104\Bitcoin\Services;

use jwz104\Bitcoin\Models\BitcoinUser;
use jwz104\Bitcoin\Models\BitcoinTransaction;

class TransactionService {

    /**
     * The bitcoin user
     *
     * @var jwz104\Bitcoin\Models\BitcoinUser
     */
    protected $bitcoinuser;

    /**
     * Instantiate a new HomepageController instance.
     *
     * @param $user jwz104\Bitcoin\Models\BitcoinUser The bitcoin user
     * @return void
     */
    public function __construct(BitcoinUser $user)
    {
        $this->bitcoinuser = $user;
    }

    /**
     * Transfer to account and return true is success
     *
     * @param $user jwz104\Bitcoin\Models\BitcoinUser The user to pay
     * @param $amount double The amount of bitcoins to send
     * @return bool
     */
    public function transferToUser(BitcoinUser $user, $amount)
    {
        //Check if balance is high enough
        if ($this->user->balance() < $amount) {
            return false;
        }

        $transaction = new BitcoinTransaction();

        $transaction->bitcoin_user_id = $this->bitcoinuser->id;
        $transaction->amount = $amount;
        $transaction->type = 'account';
        $transaction->other_bitcoin_user_id = $user->id;

        $transaction->save();
        return true;
    }
}
