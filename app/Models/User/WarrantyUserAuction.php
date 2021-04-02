<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

class WarrantyUserAuction extends Model
{
    //
    protected $fillable = ['id', 'user_id', 'auction_id', 'amount'];

    protected $fakeFields = ['id', 'user_id', 'auction_id', 'amount'];

}
