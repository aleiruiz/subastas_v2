<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class SystemNotice extends Model
{
    protected $fillable = ['title', 'description', 'type', 'start_at', 'end_at', 'is_active', 'created_by'];

    protected $fakeFields = ['title', 'description', 'type', 'start_at', 'end_at', 'is_active', 'created_by'];
}
