<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = ['user_id', 'first_name', 'last_name', 'address', 'phone'];

    protected $fakeFields = ['user_id', 'first_name', 'last_name', 'address', 'phone'];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
