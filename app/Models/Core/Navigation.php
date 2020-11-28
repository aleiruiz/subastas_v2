<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;

class Navigation extends Model
{
    protected $fillable = ['slug', 'items'];

    protected $fakeFields = ['slug', 'items'];

    public function getItemsAttribute($value){

        return json_decode($value, true);

    }

    public function setItemsAttribute($value){

        return $this->attributes['items'] =json_encode($value);

    }
}
