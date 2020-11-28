<?php

namespace App\Jobs;

use App\Services\User\ReleaseAuctionMoneyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AuctionMoneyRelease implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    private $auctionId;

    /**
     * Create a new job instance.
     *
     * @param $auctionId
     */
    public function __construct($auctionId)
    {
        $this->auctionId = $auctionId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        app(ReleaseAuctionMoneyService::class)->releaseAuctionMoney($this->auctionId);
    }
}
