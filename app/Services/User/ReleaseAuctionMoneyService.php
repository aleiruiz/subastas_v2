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

class ReleaseAuctionMoneyService
{
    protected $auction;

    public function __construct(AuctionInterface $auction)
    {
        $this->auction = $auction;
    }

    public function releaseAuctionMoney($auctionId)
    {

        $auction = $this->auction->getFirstByConditions(['id' => $auctionId]);
        if (empty($auction) || $auction->status != AUCTION_STATUS_COMPLETED) {
            return false;
        }
        $date = now();

        try {

            DB::beginTransaction();

            $isWinner = app(BidInterface::class)->getFirstByConditions(['auction_id' => $auctionId, 'is_winner' => AUCTION_WINNER_STATUS_WIN]);
            $parameters['product_claim_status'] = AUCTION_PRODUCT_CLAIM_STATUS_DELIVERED;

            if (!$this->auction->update($parameters, $auctionId)) {
                throw new Exception('Failed to update auction in money release function.');
            }
            if ($auction->auction_type == AUCTION_TYPE_VICKREY_AUCTION && count($auction->bids) > 1)
            {
                $winnerPayableAmount = $auction->bids()->orderBy('amount', 'desc')->orderBy('id', 'asc')->skip(1)->first();
                $winnerAmount = $winnerPayableAmount->amount;

            } else {
                $winnerAmount = $isWinner->amount;
            }

            $auctionFeeType = settings('auction_fee_type');
            $auctionFeeInPercent = settings('auction_fee_in_percent');
            $amountOfAuctionFeeInPercent = bcdiv(bcmul($winnerAmount, $auctionFeeInPercent), "100");
            $auctionFeeInFixedAmount = settings('auction_fee_in_fixed_amount');

            if ($auctionFeeType == AUCTION_FEE_IN_PERCENT) {
                $totalFee = $amountOfAuctionFeeInPercent;
            } elseif ($auctionFeeType == AUCTION_FEE_IN_FIXED_AMOUNT) {
                $totalFee = $auctionFeeInFixedAmount;
            } else($auctionFeeType == AUCTION_FEE_IN_BOTH_AMOUNT)
            {
                $totalFee = bcadd($amountOfAuctionFeeInPercent, $auctionFeeInFixedAmount)
            };

            $sellerAmount = bcsub($winnerAmount, $totalFee);

            $walletAttributes = [
                [
                    'conditions' => ['user_id' => $isWinner->user_id, 'currency_id' => $auction->currency_id],
                    'fields' => [
                        'on_order' => ['decrement', $winnerAmount],
                        'updated_at' => $date
                    ]
                ],
                [
                    'conditions' => ['user_id' => $auction->seller->user_id, 'currency_id' => $auction->currency_id],
                    'fields' => [
                        'balance' => ['increment', $sellerAmount],
                        'updated_at' => $date
                    ],
                ],
                [
                    'conditions' => ['user_id' => FIXED_USER_SUPER_ADMIN, 'currency_id' => $auction->currency_id],
                    'fields' => [
                        'balance' => ['increment', $totalFee],
                        'updated_at' => $date
                    ],
                ],
            ];

            $walletUpdateCount = app(WalletInterface::class)->bulkUpdate($walletAttributes);

            if ($walletUpdateCount != count($walletAttributes)) {
                throw new Exception('Failed to bid please try again later');
            }


            $refId = Str::uuid();

            $sellerWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => $auction->seller->user_id]);
            $adminWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => FIXED_USER_SUPER_ADMIN]);
            $userWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => $isWinner->user_id]);

            $transactionAttributes = [

//                    to user as auction cost on seller
                [
                    'user_id' => $isWinner->user_id,
                    'wallet_id' => $userWallet->id,
                    'model_id' => $userWallet->id,
                    'model' => get_class($userWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($winnerAmount, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => DECREASED_FROM_ESCROW_TO_SELLER_WALLET_ON_AUCTION_COMPLETION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => $isWinner->user_id,
                    'wallet_id' => $userWallet->id,
                    'model_id' => $userWallet->id,
                    'model' => get_class($userWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($winnerAmount, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => INCREASED_TO_SELLER_WALLET_FROM_ESCROW_ON_AUCTION_COMPLETION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],

//                    to seller as selling amount
                [
                    'user_id' => $auction->seller->user_id,
                    'wallet_id' => $sellerWallet->id,
                    'model_id' => $sellerWallet->id,
                    'model' => get_class($sellerWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($sellerAmount, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => INCREASED_TO_SELLER_WALLET_FROM_ESCROW_ON_AUCTION_COMPLETION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => $auction->seller->user_id,
                    'wallet_id' => $sellerWallet->id,
                    'model_id' => $sellerWallet->id,
                    'model' => get_class($sellerWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($sellerAmount, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => DECREASED_FROM_ESCROW_TO_SELLER_WALLET_ON_AUCTION_COMPLETION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],

//                    to seller as auction fee on admin
                [
                    'user_id' => $auction->seller->user_id,
                    'wallet_id' => $sellerWallet->id,
                    'model_id' => $sellerWallet->id,
                    'model' => get_class($sellerWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($totalFee, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => INCREASED_TO_SYSTEM_WALLET_FROM_SELLER_WALLET_AS_SELLING_FEE,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => $auction->seller->user_id,
                    'wallet_id' => $sellerWallet->id,
                    'model_id' => $sellerWallet->id,
                    'model' => get_class($sellerWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($totalFee, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => DECREASED_FROM_SELLER_WALLET_TO_SYSTEM_WALLET_AS_SELLING_FEE,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],

//                    to admin as system fee
                [
                    'user_id' => FIXED_USER_SUPER_ADMIN,
                    'wallet_id' => $adminWallet->id,
                    'model_id' => $adminWallet->id,
                    'model' => get_class($adminWallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($totalFee, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => FIXED_USER_SUPER_ADMIN,
                    'model' => get_class($adminWallet),
                    'wallet_id' => $adminWallet->id,
                    'model_id' => $adminWallet->id,
                    'ref_id' => $refId,
                    'amount' => bcmul($totalFee, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => DECREASED_FROM_ESCROW_TO_SELLER_WALLET_ON_AUCTION_COMPLETION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
            ];

            app(TransactionInterface::class)->insert($transactionAttributes);
            $route = route('user-currency.index');
            $notificationAttributes = [
                [
                    'user_id' => $auction->seller->user_id,
                    'data' => __(':currency :amount has been added to your account as you have completed your :auction..', ['currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $sellerAmount . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>']),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => $auction->seller->user_id,
                    'data' => __('you have been charged :currency :amount as you sold your product by :auction..', ['currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $totalFee . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>']),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => FIXED_USER_SUPER_ADMIN,
                    'data' => __('Received :currency :amount as :auction completion fee', ['currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $totalFee . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>',]),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ]
            ];

            app(NotificationInterface::class)->insert($notificationAttributes);
            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();
            return false;
        }

        return true;
    }
}
