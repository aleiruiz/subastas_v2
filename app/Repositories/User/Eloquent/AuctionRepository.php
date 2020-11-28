<?php

namespace App\Repositories\User\Eloquent;

use App\Models\User\Auction;
use App\Repositories\BaseRepository;
use App\Repositories\User\Interfaces\AuctionInterface;
use Carbon\Carbon;

class AuctionRepository extends BaseRepository implements AuctionInterface
{
    /**
     * @var Auction
     */
    protected $model;

    public function __construct(Auction $auction)
    {
        $this->model = $auction;
    }

    public function getRecentAuction($limit)
    {
        return $this->model->where(['status' => AUCTION_STATUS_RUNNING])->orderBy('id', 'desc')->take($limit)->get();
    }

    public function getPopularAuction($limit)
    {
        return $this->model->withCount('bids')->where(['status' => AUCTION_STATUS_RUNNING])
            ->orderBy('bids_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function highestBidderAuction($limit)
    {
        return $this->model->where(['auction_type' => AUCTION_TYPE_HIGHEST_BIDDER, 'status' => AUCTION_STATUS_RUNNING])->orderBy('id', 'desc')->take($limit)->get();
    }

    public function blindBidderAuction($limit)
    {
        return $this->model->where(['auction_type' => AUCTION_TYPE_BLIND_BIDDER, 'status' => AUCTION_STATUS_RUNNING])->orderBy('id', 'desc')->take($limit)->get();
    }

    public function uniqueBidderAuction($limit)
    {
        return $this->model->where(['auction_type' => AUCTION_TYPE_UNIQUE_BIDDER, 'status' => AUCTION_STATUS_RUNNING])->orderBy('id', 'desc')->take($limit)->get();
    }

    public function vickeryBidderAuction($limit)
    {
        return $this->model->where(['auction_type' => AUCTION_TYPE_VICKREY_AUCTION, 'status' => AUCTION_STATUS_RUNNING])->orderBy('id', 'desc')->take($limit)->get();
    }

    public function lowestPriceAuction($limit)
    {
        return $this->model->where(['status' => AUCTION_STATUS_RUNNING])->orderBy('bid_initial_price', 'asc' )->take($limit)->get();
    }

    public function highestPriceAuction($limit)
    {
        return $this->model->where(['status' => AUCTION_STATUS_RUNNING])->orderBy('bid_initial_price', 'desc' )->take($limit)->get();
    }

    public function getTodayCompletion()
    {
        $currentDate = Carbon::now()->subDay();
        return $this->model->where('status', AUCTION_STATUS_RUNNING)
            ->whereDate('ending_date', '>=', $currentDate)
            ->whereDate('ending_date', '<=', $currentDate)
            ->get();
    }

    public function getUnreleased()
    {
        $disputeTime = settings('dispute_time', true);
        $currentDate = Carbon::now()->subDays($disputeTime);
        return $this->model->where('status', AUCTION_STATUS_COMPLETED)
            ->where('product_claim_status', AUCTION_PRODUCT_CLAIM_STATUS_ON_SHIPPING)
            ->whereDate('ending_date', '>=', $currentDate)
            ->whereDate('ending_date', '<=', $currentDate)
            ->get();


    }

}
