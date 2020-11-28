<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['user_id', 'data', 'link', 'read_at'];

    protected $fakeFields = ['user_id', 'data', 'link', 'read_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
