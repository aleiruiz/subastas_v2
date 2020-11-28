<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    protected $fillable = ['name', 'short_code', 'icon', 'is_active'];

    protected $fakeField = ['name', 'short_code', 'icon', 'is_active'];
}
