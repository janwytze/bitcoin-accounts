<?php

namespace jwz104\Bitcoin\Models;

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
     * @return jwz104\Bitcoin\BitcoinUser
     */
    public function user()
    {
        return $this->belongsTo('jwz104\Bitcoin\BitcoinUser', 'bitcoin_account_id', 'id');
    }

    /**
     * The transactions with this address
     *
     * @return jwz104\Bitcoin\BitcoinTransaction
     */
    public function transactions()
    {
        return $this->hasMany('jwz104\Bitcoin\BitcoinTransaction', 'bitcoin_address_id', 'id');
    }
}
