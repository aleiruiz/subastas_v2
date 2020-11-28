<?php

namespace App\Jobs;

use App\Repositories\User\Interfaces\AuctionInterface;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckAuctionCompletion implements ShouldQueue
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

        $auctions = $auctionRepository->getTodayCompletion();

        if (!$auctions->isEmpty()) {
            foreach ($auctions as $auction) {
                dispatch(new AuctionWinningProcessJob($auction->id));
            }
        }

    }
}
