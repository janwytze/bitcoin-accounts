<?php

namespace jwz104\Bitcoin\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinTransaction extends Model
{
    protected $table = 'bitcoin_transactions';

    protected $fillable = [
        'bitcoin_address_id', 'bitcoin_user_id',
    ];

    public function bitcoinuser()
    {
        return $this->belongsTo('jwz104\Bitcoin\BitcoinUser', 'bitcoin_account_id', 'id');
    }
}
