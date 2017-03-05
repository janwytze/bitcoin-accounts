<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinUser extends Model
{
    /**
     * The table name
     *
     * @var string
     */
    protected $table = 'bitcoin_users';

    /**
     * The fillable columns
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The addresses of the user
     *
     * @return Jwz104\BitcoinAccounts\BitcoinAddress[]
     */
    public function addresses()
    {
        return $this->hasMany('Jwz104\BitcoinAccounts\Models\BitcoinAddress', 'bitcoin_user_id', 'id');
    }
    
    /**
     * The transactions of the user
     *
     * @return Jwz104\Bitcoin\BitcoinTransaction[]
     */
    public function transactions()
    {
        return $this->hasMany('Jwz104\BitcoinAccounts\Models\BitcoinTransaction', 'bitcoin_user_id', 'id')->orWhere('other_bitcoin_user_id', $this->id);
    }

    /**
     * Get the user balance from the users transactions
     * 
     * @return double
     */
    public function balance()
    {
        $balance = 0;

        foreach ($this->transactions as $transaction) {
            if ($transaction->type == 'sent') {
                //Also add fee
                $balance += ($transaction->amount + $transaction->fee);
            } elseif ($transaction->type == 'receive') {
                $balance += ($transaction->amount);
            } elseif ($transaction->type == 'account') {
                //Don't use transactions to same account
                if ($transaction->bitcoin_user_id == $this->id && $transaction->other_bitcoin_user_id != $this->id) {
                    $balance -= $transaction->amount;
                } elseif ($transaction->other_bitcoin_user_id == $this->id && $transaction->bitcoin_user_id != $this->id) {
                    $balance += $transaction->amount;
                }
            }
        }

        return $balance;
    }
}
