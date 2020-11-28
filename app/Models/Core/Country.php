<?php

namespace App\Models\Core;

use App\Models\User\Address;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'name',
        'code',
        'phone_code',
        'is_active',
    ];

    protected $fakeFields = [
        'name',
        'code',
        'phone_code',
        'is_active',
    ];

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
