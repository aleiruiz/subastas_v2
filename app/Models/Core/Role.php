<?php

namespace App\Models\Core;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'permissions'];

    protected $fakeFields = ['name', 'permissions'];

    public function getPermissionsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setPermissionsAttribute($value)
    {
        $this->attributes['permissions'] = json_encode($value);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
