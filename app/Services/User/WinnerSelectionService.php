<?php

namespace App\Services\User;

use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WinnerSelectionService
{
    protected $bid;

    public function __construct(BidInterface $bid)
    {
        $this->bid = $bid;
    }

    public function winnerSelector($auctionId)
    {
        $auction = app(AuctionInterface::class)->getFirstByConditions(['id' => $auctionId]);
        if ($auction->status != AUCTION_STATUS_RUNNING) {
            return false;
        }

        if ($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER || $auction->auction_type == AUCTION_TYPE_VICKREY_AUCTION) {
            $this->highestBidWinner($auction);
        }

        if ($auction->auction_type == AUCTION_TYPE_BLIND_BIDDER) {
            $this->blindBidWinner($auction);
        }

        if ($auction->auction_type == AUCTION_TYPE_VICKREY_AUCTION) {
            $this->vickreyBidWinner($auction);
        }

        if ($auction->auction_type == AUCTION_TYPE_UNIQUE_BIDDER) {
            $this->uniqueBidWinner($auction);
        }

    }

    public function highestBidWinner($auction)
    {
        $highestBid = $auction->bids()->orderBy('amount', 'desc')->orderBy('id', 'asc')->first();
        if (empty($highestBid)) {
            return false;
        }

        $parameter['is_winner'] = AUCTION_WINNER_STATUS_WIN;
        $changeAuctionStatus['status'] = AUCTION_STATUS_COMPLETED;

        $updateAsWinner = $this->bid->update($parameter, $highestBid->id);
        $completeAuction = app(AuctionInterface::class)->update($changeAuctionStatus, $auction->id);

        if (!$updateAsWinner && !$completeAuction)
        {
            return false;
        }

        $date = now();
        $route = route('shipping-description.create', ['id' => $auction->id]);
        $notificationAttributes = [
            'user_id' => $updateAsWinner->user_id,
            'data' => __('You just won the :auction, please submit your address', ['auction' => '<strong>' . $auction->title . '</strong>']),
            'link' => $route,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        app(NotificationInterface::class)->insert($notificationAttributes);

        return true;
    }

    public function blindBidWinner($auction)
    {
        $highestBid = $auction->bids()->orderBy('amount', 'desc')->orderBy('id', 'asc')->first();

        if (empty($highestBid)) {
            return false;
        }

        $parameter['is_winner'] = AUCTION_WINNER_STATUS_WIN;
        $changeAuctionStatus['status'] = AUCTION_STATUS_COMPLETED;

        $date = now();
        $refId = Str::uuid();
        $walletRoute = route('user-currency.index');

        $returnAmount = app(BidInterface::class)->returnAmount($auction->id);
        $winnerRoute = route('shipping-description.create', ['id' => $auction->id]);
        $walletAttributes = [];
        $transactionAttributes = [];
        $notificationAttributes = [];

        foreach ($returnAmount as $bidder) {
            $refundableAmount = $bidder->amount;
            if ($bidder->amount == $highestBid->amount && $bidder->user_id == $highestBid->user_id) {
                $refundableAmount = 0;
            }
            if ($refundableAmount) {
                $walletAttributes[] =
                    [
                        'conditions' => ['user_id' => $bidder->user_id, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'on_order' => ['decrement', $refundableAmount],
                            'balance' => ['increment', $refundableAmount],
                            'updated_at' => $date,
                        ]
                    ];

                $transactionAttributes[] =
                    [
                        'user_id' => $bidder->user_id,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'ref_id' => $refId,
                        'amount' => bcmul($refundableAmount, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_ESCROW_TO_USER_WALLET_ON_BIDDING_REVERSAL,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ];
                $transactionAttributes[] =
                    [
                        'user_id' => $bidder->user_id,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'ref_id' => $refId,
                        'amount' => bcmul($refundableAmount, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_USER_WALLET_FROM_ESCROW_ON_BIDDING_REVERSAL,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ];

                $notificationAttributes[] = [
                    'user_id' => $bidder->user_id,
                    'data' => __('Your :currency :amount has been restored to your wallet on bidding to :auction', ['auction' => '<strong>' . $auction->title . '</strong>', 'currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $bidder->amount . '</strong>']),
                    'link' => $walletRoute,
                    'updated_at' => $date,
                    'created_at' => $date,
                ];
            }
        }

        $notificationAttributes[] = [
            'user_id' => $highestBid->user_id,
            'data' => __('You just won the :auction, please submit your address', ['auction' => '<strong>' . $auction->title . '</strong>']),
            'link' => $winnerRoute,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        try {

            DB::beginTransaction();

            if (!app(AuctionInterface::class)->update($changeAuctionStatus, $auction->id))
            {
                throw new Exception;
            }

            if (!$this->bid->update($parameter, $highestBid->id)) {
                throw new Exception;
            }

            if (count($walletAttributes) > 0 && app(WalletInterface::class)->bulkUpdate($walletAttributes) != count($walletAttributes)) {
                throw new Exception;
            }

            if (count($transactionAttributes) > 0 && app(TransactionInterface::class)->insert($transactionAttributes) != count($transactionAttributes)) {
                throw new Exception;
            }

            app(NotificationInterface::class)->insert($notificationAttributes);
            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
        }

        return true;
    }

    public function vickreyBidWinner($auction)
    {
        $highestBid = $auction->bids()->orderBy('amount', 'desc')->orderBy('id', 'asc')->first();
        if (empty($highestBid)) {
            return false;
        }
        $winnerPayableBid = $auction->bids()->orderBy('amount', 'desc')->orderBy('id', 'asc')->skip(1)->first();
        $adjustedBidAmount = count($auction->bids) > 1 ? bcsub($highestBid->amount, $winnerPayableBid->amount) : 0;

        $parameter['is_winner'] = AUCTION_WINNER_STATUS_WIN;
        $changeAuctionStatus['status'] = AUCTION_STATUS_COMPLETED;

        $date = now();
        $refId = Str::uuid();
        $walletRoute = route('user-currency.index');

        $returnAmount = app(BidInterface::class)->returnAmount($auction->id);
        $winnerRoute = route('shipping-description.create', ['id' => $auction->id]);
        $walletAttributes = [];
        $transactionAttributes = [];
        $notificationAttributes = [];

        foreach ($returnAmount as $bidder) {
            $refundableAmount = $bidder->amount;
            if ($bidder->amount == $highestBid->amount && $bidder->user_id == $highestBid->user_id) {
                $refundableAmount = $adjustedBidAmount;
            }
            if ($refundableAmount) {
                $walletAttributes[] =
                    [
                        'conditions' => ['user_id' => $bidder->user_id, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'on_order' => ['decrement', $refundableAmount],
                            'balance' => ['increment', $refundableAmount],
                            'updated_at' => $date,
                        ]
                    ];

                $transactionAttributes[] =
                    [
                        'user_id' => $bidder->user_id,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'ref_id' => $refId,
                        'amount' => bcmul($refundableAmount, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_ESCROW_TO_USER_WALLET_ON_BIDDING_REVERSAL,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ];
                $transactionAttributes[] =
                    [
                        'user_id' => $bidder->user_id,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'ref_id' => $refId,
                        'amount' => bcmul($refundableAmount, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_USER_WALLET_FROM_ESCROW_ON_BIDDING_REVERSAL,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ];

                $notificationAttributes[] = [
                    'user_id' => $bidder->user_id,
                    'data' => __('Your :currency :amount has been restored to your wallet on bidding to :auction', ['auction' => '<strong>' . $auction->title . '</strong>', 'currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $bidder->amount . '</strong>']),
                    'link' => $walletRoute,
                    'updated_at' => $date,
                    'created_at' => $date,
                ];
            }
        }

        $notificationAttributes[] = [
            'user_id' => $highestBid->user_id,
            'data' => __('You just won the :auction, please submit your address', ['auction' => '<strong>' . $auction->title . '</strong>']),
            'link' => $winnerRoute,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        try {

            DB::beginTransaction();

            if (!app(AuctionInterface::class)->update($changeAuctionStatus, $auction->id))
            {
                throw new Exception;
            }

            if (!$this->bid->update($parameter, $highestBid->id)) {
                throw new Exception;
            }

            if (count($walletAttributes) > 0 && app(WalletInterface::class)->bulkUpdate($walletAttributes) != count($walletAttributes)) {
                throw new Exception;
            }

            if (count($transactionAttributes) > 0 && app(TransactionInterface::class)->insert($transactionAttributes) != count($transactionAttributes)) {
                throw new Exception;
            }

            app(NotificationInterface::class)->insert($notificationAttributes);
            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
        }

        return true;
    }

    public function uniqueBidWinner($auction)
    {
        $lowestUniqueBid = app(BidInterface::class)->uniqueBid($auction->id);

        if (empty($lowestUniqueBid)) {
            return false;
        }

        $date = now();
        $refId = Str::uuid();
        $userHighestBid = app(BidInterface::class)->returnAmount($auction->id);

        $parameter['is_winner'] = AUCTION_WINNER_STATUS_WIN;
        $changeAuctionStatus['status'] = AUCTION_STATUS_COMPLETED;
        $checkWinner = app(BidInterface::class)->getFirstByConditions(['amount' => $lowestUniqueBid->amount], null, ['id' => 'asc']);
        $walletRoute = route('user-currency.index');
        $winnerRoute = route('shipping-description.create', ['id' => $auction->id]);
        $walletAttributes = [];
        $transactionAttributes = [];
        $notificationAttributes = [];

        foreach ($userHighestBid as $bidder) {
            $refundableAmount = $bidder->amount;
            if ($bidder->user_id == $checkWinner->user_id) {
                if ($bidder->amount > $checkWinner->amount) {
                    $refundableAmount = $bidder->amount - $checkWinner->amount;
                } else {
                    $refundableAmount = 0;
                }
            }
            if ($refundableAmount) {
                $walletAttributes[] = [
                        'conditions' => ['user_id' => $bidder->user_id, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'on_order' => ['decrement', $refundableAmount],
                            'balance' => ['increment', $refundableAmount],
                        ]
                ];
                $transactionAttributes[] = [
                        'user_id' => $bidder->user_id,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'ref_id' => $refId,
                        'amount' => bcmul($refundableAmount, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_ESCROW_TO_USER_WALLET_ON_BIDDING_REVERSAL,
                        'updated_at' => $date,
                        'created_at' => $date,
                ];
                $transactionAttributes[] = [
                        'user_id' => $bidder->user_id,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'ref_id' => $refId,
                        'amount' => bcmul($refundableAmount, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_USER_WALLET_FROM_ESCROW_ON_BIDDING_REVERSAL,
                        'updated_at' => $date,
                        'created_at' => $date,
                ];
                $notificationAttributes[] = [
                        'user_id' => $bidder->user_id,
                        'data' => __('Your :currency :amount has been restored to your wallet on bidding to :auction', ['auction' => '<strong>' . $auction->title . '</strong>', 'currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $bidder->amount . '</strong>']),
                        'link' => $walletRoute,
                        'updated_at' => $date,
                        'created_at' => $date,
                ];
            }
        }

        $notificationAttributes[] = [
            'user_id' => $checkWinner->user_id,
            'data' => __('You just won the :auction, please submit your address here', ['auction' => '<strong>' . $auction->title . '</strong>']),
            'link' => $winnerRoute,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        try {

            DB::beginTransaction();

            if (!app(AuctionInterface::class)->update($changeAuctionStatus, $auction->id))
            {
                throw new Exception;
            }

            $winner = app(BidInterface::class)->update($parameter, $checkWinner->id);

            if (empty($winner)) {
                throw new Exception;
            }

            if (count($walletAttributes) > 0 && app(WalletInterface::class)->bulkUpdate($walletAttributes) != count($walletAttributes)) {
                throw new Exception;
            }

            if (count($transactionAttributes) > 0 && app(TransactionInterface::class)->insert($transactionAttributes) != count($transactionAttributes)) {
                throw new Exception;
            }

            app(NotificationInterface::class)->insert($notificationAttributes);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
        }

        return true;
    }
}
