<?php

namespace App\Models\Core;

use App\Models\User\Address;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    protected $fillable = [
        'country_id',
        'name',
        'short_name',
        'is_active',
    ];

    protected $fakeFields = [
        'country_id',
        'name',
        'short_name',
        'is_active',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }
}
