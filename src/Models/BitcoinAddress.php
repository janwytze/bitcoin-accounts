<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinAddress extends Model
{
    /**
     * The table name
     *
     * @var string
     */
    protected $table = 'bitcoin_accounts';

    /**
     * The fillable columns
     *
     * @var string[]
     */
    protected $fillable = [
        'address', 'bitcoin_user_id',
    ];

    /**
     * The bitcoin user
     *
     * @return Jwz104\BitcoinAccounts\BitcoinUser
     */
    public function user()
    {
        return $this->belongsTo('Jwz104\BitcoinAccounts\BitcoinUser', 'bitcoin_account_id', 'id');
    }

    /**
     * The transactions with this address
     *
     * @return Jwz104\BitcoinAccounts\BitcoinTransaction
     */
    public function transactions()
    {
        return $this->hasMany('Jwz104\BitcoinAccounts\BitcoinTransaction', 'bitcoin_address_id', 'id');
    }
}
