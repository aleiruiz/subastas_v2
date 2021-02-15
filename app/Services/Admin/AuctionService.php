<?php

namespace App\Services\Admin;

use App\Repositories\Admin\Interfaces\CategoryInterface;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\CommentInterface;
use App\Services\Core\DataListService;
use Carbon\Carbon;

class AuctionService
{
    protected $auction;

    public function __construct(AuctionInterface $auction)
    {
        $this->auction = $auction;
    }

    public function auctionDetails($id)
    {
        $data['auction'] = $this->auction->findOrFailById($id);

        $data['auctionId'] = $id;
        $data['carbon'] = new Carbon();
        $data['defaultAddress'] = $data['auction']->seller->address()->where('is_default', ACTIVE_STATUS_ACTIVE)->first();
        $data['categories'] = app(CategoryInterface::class)->getAll()->pluck('name', 'id')->toArray();
        $data['comments'] = app(CommentInterface::class)->getByConditions(['auction_id' => $id]);
        $data['lastBid'] =  $data['auction']->bids()->orderBy('id', 'desc')->first();
        if (auth()->check())
        {
            $data['userLastBid'] = $data['auction']->bids()->orderBy('id', 'desc')->where('user_id', auth()->user()->id)->first();
        }
        $data['highestBid'] = $data['auction']->bids()->orderBy('amount', 'desc')->first();

        $bids = app(BidInterface::class);
        $data['isWinner'] = $bids->getFirstByConditions(['auction_id' => $id, 'is_winner' => AUCTION_WINNER_STATUS_WIN]);
        if(!is_null($data['isWinner']))
        {
            $data['address'] = app(AddressInterface::class)->getFirstByConditions(['id' => $data['isWinner']->auction->address_id]);
        }

        $where = ['auction_id' => $id];
        $query = $bids->paginateWithFilters([], null, $where);
        $data['list'] = app(DataListService::class)->dataList($query, null);
        $data['title'] = __('Auction Details');

        return $data;
    }
}
