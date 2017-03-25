<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinHoldTransaction extends Model
{
    protected $fillable = [
        'bitcoin_user_id',
        'address',
        'amount',
        'fee',
        'type',
    ];

    public function user()
    {
        return $this->belongsTo('Jwz104\BitcoinAccounts\Models\BitcoinUser', 'bitcoin_user_id', 'id');
    }
}
