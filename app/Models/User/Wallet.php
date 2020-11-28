<?php

namespace App\Models\User;

use App\Models\Admin\Currency;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'currency_id',
        'balance',
        'on_order',
        'is_system',
    ];

    protected $fakeFields = [
        'user_id',
        'currency_id',
        'balance',
        'on_order',
        'is_system',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
