<?php

namespace App\Models\User;

use App\Models\Core\Role;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['username', 'password', 'email', 'ref_id', 'role_id', 'is_address_verified', 'is_id_verified', 'remember_me', 'avatar', 'is_email_verified', 'is_financial_active', 'is_accessible_under_maintenance', 'is_active', 'created_by'];

    protected $fakeFields = ['username', 'password', 'email', 'ref_id', 'role_id', 'is_address_verified', 'is_id_verified', 'remember_me', 'avatar', 'is_email_verified', 'is_financial_active', 'is_accessible_under_maintenance', 'is_active', 'created_by'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function whereIn(string $string, array $array)
    {
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

//auction relations

    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function addresses()
    {
        return $this->morphMany(Address::class, 'ownerable');
    }

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function disputes()
    {
        return $this->hasMany(Dispute::class);
    }

    public function knowYourCustomers()
    {
        return $this->hasMany(KnowYourCustomer::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

}
