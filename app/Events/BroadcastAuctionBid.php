<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastAuctionBid implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    private $bid;

    /**
     * Create a new event instance.
     *
     * @param $bid
     */
    public function __construct($bid)
    {
        //
        $this->bid = $bid;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('auction-bid');
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        $endTime = \Carbon\Carbon::parse($this->bid->auction->ending_date)->format('M d\\, Y H:i:s');
        return [
            'user_id' => $this->bid->user_id,
            'username' => $this->bid->user->username,
            'amount' => $this->bid->amount,
            'currency' => $this->bid->auction->currency->symbol,
            'date' => $this->bid->created_at->toDateTimeString(),
            'end_time' => $endTime,
            'bid_count' => $this->bid->auction->bids->count(),
            'bigger_bid' => $this->bid->auction->bids->max('amount'),
            'bid_increment_dif' => $this->bid->auction->bid_increment_dif
        ];
    }

    /**
     * Determine if this event should broadcast.
     *
     * @return bool
     */
    public function broadcastWhen()
    {
        return ($this->bid->auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER);
    }
}
