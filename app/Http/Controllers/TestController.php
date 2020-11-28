<?php

namespace App\Http\Controllers;


use App\Jobs\AuctionWinningProcessJob;
use App\Jobs\CheckAuctionCompletion;
use App\Models\User\Auction;
use App\Models\User\Seller\Seller;
use App\Models\User\Wallet;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Services\User\BidService;
use App\Services\User\ReleaseAuctionMoneyService;
use App\Services\User\WinnerSelectionService;
use Carbon\Carbon;
use Facades\Codemen\Installer\Services\FormGenerator;

class TestController extends Controller
{

    protected $service;

    public function __construct(WinnerSelectionService $service)
    {
        $this->service = $service;
    }

    public function test(AuctionInterface $auctionRepository)
    {

        $auction = app(AuctionInterface::class)->getFirstByConditions(['id' => 1]);

        $response = CheckAuctionCompletion::dispatch($auction->id)->delay(now()->addSeconds(60));

        dd($response);

    }

    public function myFunction()
    {
        return 'OK';
    }

    public function testPost()
    {

    }

    function numberToString($number)
    {
        $number = sprintf('%.25f', floatval($number));
        return $number;
    }
}

