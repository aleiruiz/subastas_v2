<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\BidRequest;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Services\User\BidService;

class BidController extends Controller
{

    protected $service;

    public function __construct(BidService $service)
    {
        $this->service = $service;
    }

    public function store(BidRequest $request, $auctionId)
    {
        $isIdVerificationRequired = settings('is_id_verified');
        $isAddressVerifiedRequired = settings('is_address_verified');

        if ($isIdVerificationRequired == ACTIVE_STATUS_ACTIVE) {
            $idVerification = auth()->user()->is_id_verified;

            if ($idVerification !== ACTIVE_STATUS_ACTIVE) {
                return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('ID verification is required'));
            }
        }

        if ($isAddressVerifiedRequired == ACTIVE_STATUS_ACTIVE) {
            $addressVerification = auth()->user()->is_address_verified;

            if ($addressVerification !== ACTIVE_STATUS_ACTIVE) {
                return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Address verification is required'));
            }
        }

        $conditions = [
            'id' => $auctionId,
            'status' => AUCTION_STATUS_RUNNING
        ];

        $auction = app(AuctionInterface::class)->getFirstByConditions($conditions);

        if (!$auction) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Invalid Request!'));
        }

        $onBiddingUserWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => auth()->id(), 'currency_id' => $auction->currency_id], 'currency');

        if ($onBiddingUserWallet->currency->is_active != ACTIVE_STATUS_ACTIVE) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Sorry, The :currency currency is unavailable right now', ['currency' => $onBiddingUserWallet->currency->symbol]));
        }

        if ($onBiddingUserWallet->balance < $request->amount) {
            return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('You do not have sufficient balance'));
        }

        $parameters = $request->only('amount');
        $parameters['user_id'] = auth()->id();
        $parameters['auction_id'] = $auction->id;

        if ($auction->is_multiple_bid_allowed != ACTIVE_STATUS_ACTIVE) {
            $checkBid = app(BidInterface::class)->getFirstByConditions(['user_id' => auth()->id(), 'auction_id' => $auctionId]);
            if ($checkBid) {
                return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('You can not bid again, Multiple bid is not allowed'));
            }
        }

        if ($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER) {
            $response = $this->service->highestBidder($auction, $parameters, $onBiddingUserWallet);
            return redirect()->back()->with($response[SERVICE_RESPONSE_STATUS], $response[SERVICE_RESPONSE_MESSAGE]);
        }

        if ($auction->auction_type == AUCTION_TYPE_BLIND_BIDDER) {
            $response = $this->service->blindBidder($auction, $parameters);
            return redirect()->back()->with($response[SERVICE_RESPONSE_STATUS], $response[SERVICE_RESPONSE_MESSAGE]);
        }


        if ($auction->auction_type == AUCTION_TYPE_VICKREY_AUCTION) {
            $response = $this->service->vickreyBidder($auction, $parameters);
            return redirect()->back()->with($response[SERVICE_RESPONSE_STATUS], $response[SERVICE_RESPONSE_MESSAGE]);
        }

        if ($auction->auction_type == AUCTION_TYPE_UNIQUE_BIDDER) {
            $response = $this->service->uniqueBidder($auction, $parameters);
            return redirect()->back()->with($response[SERVICE_RESPONSE_STATUS], $response[SERVICE_RESPONSE_MESSAGE]);
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to place the bid.'));

    }

}
