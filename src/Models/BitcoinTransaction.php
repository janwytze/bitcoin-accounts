<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinTransaction extends Model
{
    protected $table = 'bitcoin_transactions';

    protected $fillable = [
        'bitcoin_address_id', 'bitcoin_user_id',
    ];

    public function bitcoinuser()
    {
        return $this->belongsTo('Jwz104\BitcoinAccounts\Models\BitcoinUser', 'bitcoin_user_id', 'id');
    }

    public function address()
    {
        return $this->belongsTo('Jwz104\BitcoinAccounts\Models\BitcoinAddress', 'bitcoin_address_id', 'id');
    }
}
