<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinAddress extends Model
{
    /**
     * The fillable columns
     *
     * @var string[]
     */
    protected $fillable = [
        'bitcoin_user_id',
        'address',
    ];

    /**
     * The bitcoin user
     *
     * @return Jwz104\BitcoinAccounts\BitcoinUser
     */
    public function user()
    {
        return $this->belongsTo('Jwz104\BitcoinAccounts\Models\BitcoinUser', 'bitcoin_user_id', 'id');
    }

    /**
     * The transactions with this address
     *
     * @return Jwz104\BitcoinAccounts\BitcoinTransaction
     */
    public function transactions()
    {
        return $this->hasMany('Jwz104\BitcoinAccounts\Models\BitcoinTransaction', 'bitcoin_address_id', 'id');
    }
}
