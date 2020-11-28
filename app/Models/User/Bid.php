<?php

namespace App\Models\User;

use App\Events\BroadcastAuctionBid;
use Illuminate\Database\Eloquent\Model;

class Bid extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
        'amount',
        'is_winner',
    ];

    protected $fakeFields = [
        'user_id',
        'auction_id',
        'amount',
        'is_winner',
    ];

    protected $dispatchesEvents = [
        'created' => BroadcastAuctionBid::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function auction()
    {
        return $this->belongsTo(Auction::class);
    }
}
