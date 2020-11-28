<?php

namespace App\Models\Admin;

use App\Models\User\Auction;
use App\Models\User\Wallet;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'name',
        'symbol',
        'logo',
        'is_active',
        'deposit_status',
        'min_deposit',
        'withdrawal_status',
        'min_withdrawal',
    ];
    protected $fakeFields = [
        'name',
        'symbol',
        'logo',
        'is_active',
        'deposit_status',
        'min_deposit',
        'withdrawal_status',
        'min_withdrawal',
    ];

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }

    public function paymentMethods()
    {
        return $this->belongsToMany(PaymentMethod::class, 'currency_payment_method', 'currency_id', 'payment_method_id');
    }
}
