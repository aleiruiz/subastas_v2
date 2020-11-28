<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class ApplicationSetting extends Model
{

    protected $fillable = [
        'slug',
        'value',
    ];
}
