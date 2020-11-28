<?php

namespace App\Http\Controllers;

use App\Repositories\User\Interfaces\AuctionInterface;
use App\Services\Core\DataListService;
use Illuminate\Http\Request;

class AuctionSearchController extends Controller
{
    /**
     * @var AuctionInterface
     */
    private $auction;

    public function __construct(AuctionInterface $auction)
    {

        $this->auction = $auction;
    }
    public function index()
    {

        $searchFields = [
            ['title', __('Auction Title')],
            ['product_description', __('Description')],
        ];

        $data['auctions'] = $this->auction->paginateWithFilters($searchFields);
        $data['title'] = 'All Auctions';

        return view('frontend.global_access.search', $data);
    }
}
