<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Model;

class Layout extends Model
{
    protected $fillable = ['title', 'layout_type', 'total', 'is_active'];

    protected $fakeFields = ['title', 'layout_type', 'total', 'is_active'];
}
