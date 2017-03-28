<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinTransaction extends Model
{
    protected $fillable = [
        'bitcoin_user_id',
        'bitcoin_address_id',
        'txid',
        'amount',
        'fee',
        'type',
        'to_address',
        'other_bitcoin_user_id',
    ];

    public function user()
    {
        return $this->belongsTo(BitcoinUser::class);
    }

    public function other_user()
    {
        return $this->belongsTo(BitcoinUser::class);
    }

    public function address()
    {
        return $this->belongsTo(BitcoinAddress::class);
    }
}
