<?php

namespace App\Http\Controllers;

use App\Repositories\Admin\Interfaces\LayoutInterface;
use App\Repositories\Admin\Interfaces\SliderInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Services\Core\DataListService;

class HomeController extends Controller
{
    private $auction;

    public function __construct(AuctionInterface $auction)
    {
        $this->auction = $auction;
    }

    public function index()
    {

        $data['slider'] = app(SliderInterface::class)->getDefaultSlider();

        $layouts = app(LayoutInterface::class)->getByConditions(['is_active' => ACTIVE_STATUS_ACTIVE]);
        $auctionRepository = app(AuctionInterface::class);
        $layoutViews = [];
        foreach ($layouts as $layout) {
            if ($layoutFunction = get_layout_function($layout->layout_type)) {
                $title = $layout->title;
                $auctions = $auctionRepository->{$layoutFunction}($layout->total);
                $layoutViews[] = view('frontend.layouts.includes.home_auctions', compact('auctions', 'title'))->render();
            }

        }

        $data['layoutViews'] = $layoutViews;

        return view('frontend.global_access.home', $data);
    }

    public function allAuctionIndex($category = null)
    {

        $conditions = [
            'status' => AUCTION_STATUS_RUNNING
        ];

        if ($category) {
            $conditions['categories.slug'] = $category;
        }


        $routeName = 'auction.home';

        return $this->list($conditions, $routeName);
    }

    public function list($conditions, $routeName)
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
            ['auctions.status', __('Auction Status'), auction_status()],
        ];

        $joins = null;
        if (isset($conditions['categories.slug']) && $conditions['categories.slug']) {
            $joins = [
                ['categories', 'auctions.category_id', '=', 'categories.id']
            ];
        }

        $query = $this->auction->paginateWithFilters($searchFields, $orderFields, $conditions, $filters, null, $joins, null, null, null, 9);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields, $filters);
        $data['title'] = 'All Auctions';
        $data['routeName'] = $routeName;

        return view('frontend.global_access.auction.index', $data);
    }

    public function allAuctionByTypeIndex($type = null)
    {

        $slugs = array_flip(auction_type_slug());

        if(isset($slugs[$type])){
            $type = $slugs[$type];
        }

        $conditions = [
            'status' => AUCTION_STATUS_RUNNING
        ];

        if ($type) {
            $conditions['auctions.auction_type'] = $type;
        }

        $routeName = 'auction-type.home';

        return $this->list($conditions, $routeName);
    }


}
