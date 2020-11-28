<?php

namespace App\Jobs;

use App\Repositories\User\Interfaces\AuctionInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AutomaticAuctionMoneyRelease implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @param AuctionInterface $auctionRepository
     * @return void
     */
    public function handle(AuctionInterface $auctionRepository)
    {

        $auctions = $auctionRepository->getUnreleased();

        if (!$auctions->isEmpty()) {
            foreach ($auctions as $auction) {
                dispatch(new AuctionMoneyRelease($auction->id));
            }
        }

    }
}
