<?php

namespace Jwz104\BitcoinAccounts\Models;

use Illuminate\Database\Eloquent\Model;

class BitcoinHoldTransaction extends Model
{
    /**
     * The column that are fillable
     *
     * @var string[]
     */
    protected $fillable = [
        'bitcoin_user_id',
        'address',
        'amount',
        'fee',
        'type',
    ];

    /**
     * The user that belongs to this transaction
     *
     * @return Jwz104\BitcoinAccounts\Models\BitcoinUser
     */
    public function user()
    {
        return $this->belongsTo(BitcoinUser::class);
    }
}
