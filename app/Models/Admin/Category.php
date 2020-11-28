<?php

namespace App\Models\Admin;

use App\Models\User\Auction;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'slug'];

    protected $fakeFields = ['name', 'slug'];

    public  function auctions()
    {
        return $this->hasMany(Auction::class);
    }
}
