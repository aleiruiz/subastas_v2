<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\Admin\Interfaces\CategoryInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Services\Admin\AuctionService;
use App\Services\Core\DataListService;
use App\Services\User\ReleaseAuctionMoneyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function GuzzleHttp\Promise\all;

class AuctionController extends Controller
{
    private $auction;

    public function __construct(AuctionInterface $auction)
    {
        $this->auction = $auction;
    }

    public function index()
    {
        $conditions = [];
        return $this->list($conditions);
    }

    public function completedAuctions()
    {
        $conditions = [
            'status' => AUCTION_STATUS_COMPLETED,
        ];
        return $this->list($conditions);
    }

    public function list($conditions)
    {

        $searchFields = [
            ['title', __('Auction Title')],
            ['bid_initial_price', __('Category')],
            ['product_description', __('Description')],
        ];
        $orderFields = [
            ['auction_type', __('Auction Type')],
            ['auction_status', __('Auction Status')],
            ['bid_initial_price', __('Starting Price')],
            ['category_id', __('Category')],
            ['currency_id', __('Currency')],
        ];

        $filters = [
            ['auctions.auction_type', __('Auction Type'), auction_type()],
            ['auctions.status', __('Auction Status'), auction_status()],
            ['auctions.product_claim_status', __('Product Claim Status'), product_claim_status()],
        ];

        $query = $this->auction->paginateWithFilters($searchFields, $orderFields, $conditions, $filters);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields, $filters);
        $data['title'] = 'All Auctions';

        return view('backend.auction.manage_auction.index', $data);
    }

    public function show($id)
    {
        $details = app(AuctionService::class)->auctionDetails($id);
        return view('backend.auction.manage_auction.show', $details);
    }

    public function releaseSellerMoney($id)
    {
        $currentAuction = app(AuctionInterface::class)->getFirstByConditions(['id' => $id,'status' => AUCTION_STATUS_COMPLETED]);

        if ($currentAuction->bids->count() <= 0)
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('No bidder found, No buyer posted any offer'));
        }

        if ($currentAuction->product_claim_status == AUCTION_PRODUCT_CLAIM_STATUS_DELIVERED)
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Money already released'));
        }

        $releaseMoney = app(ReleaseAuctionMoneyService::class)->releaseAuctionMoney($id);
        if (!$releaseMoney)
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to release the money'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Money release has been successful'));

    }


}
