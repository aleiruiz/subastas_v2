<?php

namespace App\Models\User;


use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    protected $fillable = [
        'name',
        'user_id',
        'ref_id',
        'description',
        'image',
        'phone_number',
        'email',
        'is_active',
    ];

    protected $fakeFields = [
        'name',
        'user_id',
        'ref_id',
        'description',
        'image',
        'phone_number',
        'email',
        'is_active',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auctions()
    {
        return $this->hasMany(Auction::class);
    }

    public function address()
    {
        return $this->morphMany(Address::class, 'ownerable');
    }
}
