<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'txn_type',
        'payment_method',
        'wallet_id',
        'amount',
        'status',
        'address',
        'payment_txn_id',
        'ref_id',
        'network_fee',
        'system_fee',
    ];

    protected $fakeFields = [
        'user_id',
        'txn_type',
        'payment_method',
        'wallet_id',
        'amount',
        'status',
        'address',
        'payment_txn_id',
        'ref_id',
        'network_fee',
        'system_fee',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
