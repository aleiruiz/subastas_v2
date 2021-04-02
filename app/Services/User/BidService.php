<?php

namespace App\Services\User;

use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Models\User\WarrantyUserAuction;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BidService
{

    protected $bid;

    public function __construct(BidInterface $bid)
    {
        $this->bid = $bid;
    }

    public function highestBidder($auction, $parameters, $onBiddingUserWallet)
    {
        $warranty = WarrantyUserAuction::where('user_id', auth()->id())
                            ->where('auction_id', $auction->id)->count();
        //dd($warranty);
        
        //start
        $highestBidderBiddingFee = settings('bidding_fee_on_highest_bidder_auction');
        $vickreyBidderBiddingFee = settings('bidding_fee_on_vickrey_bidder_auction');

        if ($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER)
        {
            $biddingFee = $highestBidderBiddingFee;
        } else {
            $biddingFee = $vickreyBidderBiddingFee;
        }

        $bidInsurance = bcadd($auction->bid_initial_price * 0.10, $biddingFee);
        $highestBid = $auction->bids()->orderBy('amount', 'desc')->first();
        //end

        if (count($auction->bids) > 0 && $auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER) {

            if (bccomp(bcadd($highestBid->amount, $auction->bid_increment_dif), $parameters['amount']) > 0) {
                return [
                    SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_ERROR,
                    SERVICE_RESPONSE_MESSAGE => __('You can not bid less than minimum bid amount'),
                ];
            }

        } else {
            if (bccomp($auction->bid_initial_price, $parameters['amount']) > 0) {
                return [
                    SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_ERROR,
                    SERVICE_RESPONSE_MESSAGE => __('You can not bid less than base price'),
                ];
            }
        }

        $walletAttributes = [
            [
                'conditions' => ['user_id' => $onBiddingUserWallet->user_id, 'currency_id' => $auction->currency_id],
                'fields' => [
                    'on_order' => ['increment', $parameters['amount']],
                    'balance' => ['decrement', $bidInsurance],
                ]
            ],
            [
                'conditions' => ['user_id' => FIXED_USER_SUPER_ADMIN, 'currency_id' => $auction->currency_id],
                'fields' => [
                    'balance' => ['increment', $biddingFee],
                ]
            ],
        ];

        try {

            DB::beginTransaction();

            if ($warranty == 0) {
                if (!app(WalletInterface::class)->bulkUpdate($walletAttributes)) {
                    throw new Exception('Failed to bid please try again later');
                }

                $date = now();
                $refId = Str::uuid();
                $transactionAttributes = $this->bidHighTransaction($onBiddingUserWallet, $refId, $bidInsurance, $biddingFee, $date);

                if (count($auction->bids) > 0 && $auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER) {
                    if (!$this->bidReversalTransaction($auction, $highestBid, $refId, $date, $transactionAttributes, $bidInsurance)) {
                        throw new Exception('Failed to bid please try again later');
                    }
                }

                $paramWarranty['user_id'] = auth()->id();
                $paramWarranty['auction_id'] = $auction->id;
                $paramWarranty['amount'] = $bidInsurance;
                WarrantyUserAuction::create($paramWarranty);
            
                app(TransactionInterface::class)->insert($transactionAttributes);
            }

            $parameters['ending_date'] = \Carbon\Carbon::now()->addSeconds((TIME_INTERVAL_AUCTION / 1000) * 3);
            $timeUpdate = app(AuctionInterface::class)->update($parameters, $auction->id);
            $bid = $this->bid->create($parameters);

            if (empty($timeUpdate)) {
                throw new Exception('Failed to update auction please try again later');
            }
            DB::commit();
            return $bid;
        } catch (Exception $exception) {
            DB::rollBack();

            return [
                SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_ERROR,
                SERVICE_RESPONSE_MESSAGE => $exception->getMessage(),
            ];
        }

        return [
            SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_SUCCESS,
            SERVICE_RESPONSE_MESSAGE => __('Your bid has been placed successfully'),
        ];

    }

    public function blindBidder($auction, $parameters)
    {
        $biddingFee = settings('bidding_fee_on_blind_bidder_auction');

        return $this->adjustBidAmount($auction, $parameters, $biddingFee);
    }

    public function vickreyBidder($auction, $parameters)
    {
        $biddingFee = settings('bidding_fee_on_vickrey_bidder_auction');

        return $this->adjustBidAmount($auction, $parameters, $biddingFee);

    }

    public function uniqueBidder($auction, $parameters)
    {
        $biddingFee = settings('bidding_fee_on_unique_bidder_auction');

        return $this->adjustBidAmount($auction, $parameters, $biddingFee);
    }

    public function bidHighTransaction($onBiddingUserWallet, $refId, $amount, $biddingFee, $date)
    {
        if ($biddingFee > 0)
        {
            $bidTransaction = [
                [
                    'user_id' => $onBiddingUserWallet->user_id,
                    'ref_id' => $refId,
                    'wallet_id' => $onBiddingUserWallet->id,
                    'model_id' => $onBiddingUserWallet->id,
                    'model' => get_class($onBiddingUserWallet),
                    'amount' => bcmul($biddingFee, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],

                [
                    'user_id' => $onBiddingUserWallet->user_id,
                    'ref_id' => $refId,
                    'wallet_id' => $onBiddingUserWallet->id,
                    'model_id' => $onBiddingUserWallet->id,
                    'model' => get_class($onBiddingUserWallet),
                    'amount' => bcmul($biddingFee, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
            ];
        }

        $bidTransaction[] =
            [
                'user_id' => $onBiddingUserWallet->user_id,
                'ref_id' => $refId,
                'wallet_id' => $onBiddingUserWallet->id,
                'model_id' => $onBiddingUserWallet->id,
                'model' => get_class($onBiddingUserWallet),
                'amount' => bcmul($amount, "-1"),
                'journal_type' => JOURNAL_TYPE_DEBIT,
                'journal' => INCREASED_TO_ESCROW_ON_BIDDING_FROM_USER_WALLET,
                'updated_at' => $date,
                'created_at' => $date,
            ];

        $bidTransaction[] =
            [
                'user_id' => $onBiddingUserWallet->user_id,
                'ref_id' => $refId,
                'wallet_id' => $onBiddingUserWallet->id,
                'model_id' => $onBiddingUserWallet->id,
                'model' => get_class($onBiddingUserWallet),
                'amount' => bcmul($amount, "1"),
                'journal_type' => JOURNAL_TYPE_CREDIT,
                'journal' => DECREASED_FROM_USER_WALLET_TO_ESCROW_ON_BIDDING,
                'updated_at' => $date,
                'created_at' => $date,
            ];

        return $bidTransaction;
    }

    public function bidTransaction($onBiddingUserWallet, $refId, $parameters, $biddingFee, $date)
    {
        if ($biddingFee > 0)
        {
            $bidTransaction = [
                [
                    'user_id' => $onBiddingUserWallet->user_id,
                    'ref_id' => $refId,
                    'wallet_id' => $onBiddingUserWallet->id,
                    'model_id' => $onBiddingUserWallet->id,
                    'model' => get_class($onBiddingUserWallet),
                    'amount' => bcmul($biddingFee, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],

                [
                    'user_id' => $onBiddingUserWallet->user_id,
                    'ref_id' => $refId,
                    'wallet_id' => $onBiddingUserWallet->id,
                    'model_id' => $onBiddingUserWallet->id,
                    'model' => get_class($onBiddingUserWallet),
                    'amount' => bcmul($biddingFee, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
            ];
        }

        $bidTransaction[] =
            [
                'user_id' => $onBiddingUserWallet->user_id,
                'ref_id' => $refId,
                'wallet_id' => $onBiddingUserWallet->id,
                'model_id' => $onBiddingUserWallet->id,
                'model' => get_class($onBiddingUserWallet),
                'amount' => bcmul($parameters['amount'], "-1"),
                'journal_type' => JOURNAL_TYPE_DEBIT,
                'journal' => INCREASED_TO_ESCROW_ON_BIDDING_FROM_USER_WALLET,
                'updated_at' => $date,
                'created_at' => $date,
            ];

        $bidTransaction[] =
            [
                'user_id' => $onBiddingUserWallet->user_id,
                'ref_id' => $refId,
                'wallet_id' => $onBiddingUserWallet->id,
                'model_id' => $onBiddingUserWallet->id,
                'model' => get_class($onBiddingUserWallet),
                'amount' => bcmul($parameters['amount'], "1"),
                'journal_type' => JOURNAL_TYPE_CREDIT,
                'journal' => DECREASED_FROM_USER_WALLET_TO_ESCROW_ON_BIDDING,
                'updated_at' => $date,
                'created_at' => $date,
            ];

        return $bidTransaction;
    }

    public function bidReversalTransaction($auction, $highestBid, $refId, $date, &$transactionAttributes, $bidInsurance)
    {
        $existedHighestBidderWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => $highestBid->user_id, 'currency_id' => $auction->currency_id]);

        $existedWalletAttributes = [
            'on_order' => DB::raw('on_order - ' . $bidInsurance),
            'balance' => DB::raw('balance + ' . $bidInsurance),
        ];

        if (!app(WalletInterface::class)->update($existedWalletAttributes, $existedHighestBidderWallet->id)) {
            return false;
        }

        $transactionAttributes[] = [
            'user_id' => $existedHighestBidderWallet->user_id,
            'ref_id' => $refId,
            'wallet_id' => $existedHighestBidderWallet->id,
            'model_id' => $existedHighestBidderWallet->id,
            'model' => get_class($existedHighestBidderWallet),
            'amount' => bcmul($bidInsurance, "1"),
            'journal_type' => JOURNAL_TYPE_DEBIT,
            'journal' => INCREASED_TO_USER_WALLET_FROM_ESCROW_ON_BIDDING_REVERSAL,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        $transactionAttributes[] = [
            'user_id' => $existedHighestBidderWallet->user_id,
            'ref_id' => $refId,
            'wallet_id' => $existedHighestBidderWallet->id,
            'model_id' => $existedHighestBidderWallet->id,
            'model' => get_class($existedHighestBidderWallet),
            'amount' => bcmul($bidInsurance, "-1"),
            'journal_type' => JOURNAL_TYPE_CREDIT,
            'journal' => DECREASED_FROM_ESCROW_TO_USER_WALLET_ON_BIDDING_REVERSAL,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        $route = route('auction.show', ['id' => $auction->id]);
        $notificationAttributes = [
            'user_id' => $highestBid->user_id,
            'data' => __('Your :currency :amount has been restored to your balance from bidding on :auction', ['currency' => '<strong>' . $existedHighestBidderWallet->currency->symbol . '</strong>', 'amount' => '<strong>' . $bidInsurance . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>']),
            'link' => $route,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        app(NotificationInterface::class)->create($notificationAttributes);

        return true;
    }

    public function adjustBidAmount($auction, $parameters, $biddingFee)
    {
        if ($auction->bid_initial_price > $parameters['amount']) {
            return [
                SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_ERROR,
                SERVICE_RESPONSE_MESSAGE => __('You can not bid less than base price'),
            ];
        }

        $totalBidAmount = bcadd($parameters['amount'], $biddingFee);
        $date = now();
        $refId = Str::uuid();
        $route = route('auction.show', ['id' => $auction->id]);
        $checkUserBid = app(BidInterface::class)->returnAmountOfCurrentUser($auction->id, auth()->id())->first();
        $bidderWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => auth()->id(), 'currency_id' => $auction->currency_id]);

        try {

            DB::beginTransaction();

            if (!is_null($checkUserBid) && $checkUserBid->amount < $parameters['amount'])
            {
                $adjustedAmount = bcsub($parameters['amount'], $checkUserBid->amount);
                $totalBidAmount = bcadd($adjustedAmount, $biddingFee);
                $walletAttributes = [
                    [
                        'conditions' => ['user_id' => $bidderWallet->user_id, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'on_order' => ['increment', $adjustedAmount],
                            'balance' => ['decrement', $totalBidAmount],
                        ]
                    ],
                    [
                        'conditions' => ['user_id' => FIXED_USER_SUPER_ADMIN, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'balance' => ['increment', $biddingFee],
                        ]
                    ],
                ];

                if (!app(WalletInterface::class)->bulkUpdate($walletAttributes)) {
                    throw new Exception('Failed to bid please try again later');
                }

                $transactionAttributes = [
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($adjustedAmount, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_USER_WALLET_TO_ESCROW_ON_BIDDING,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($adjustedAmount, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_ESCROW_ON_BIDDING_FROM_USER_WALLET,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($biddingFee, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],

                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($biddingFee, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                ];

                app(TransactionInterface::class)->insert($transactionAttributes);

                $notificationAttributes = [
                    'user_id' => auth()->id(),
                    'data' => __('Your :currency :amount has been adjusted with your last bid on :auction', ['currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $adjustedAmount . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>']),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ];

                app(NotificationInterface::class)->create($notificationAttributes);
            }

            if (!is_null($checkUserBid) && $checkUserBid->amount > $parameters['amount'])
            {
                $walletAttributes = [
                    [
                        'conditions' => ['user_id' => $bidderWallet->user_id, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'balance' => ['decrement', $biddingFee],
                            'updated_at' => $date,
                        ]
                    ],
                    [
                        'conditions' => ['user_id' => FIXED_USER_SUPER_ADMIN, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'balance' => ['increment', $biddingFee],
                            'updated_at' => $date,
                        ]
                    ],
                ];

                if (!app(WalletInterface::class)->bulkUpdate($walletAttributes)) {
                    throw new Exception('Failed to bid please try again later');
                }

                $transactionAttributes = [
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($biddingFee, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],

                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($biddingFee, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                ];

                app(TransactionInterface::class)->insert($transactionAttributes);

                $notificationAttributes = [
                    'user_id' => auth()->id(),
                    'data' => __('Your :currency :amount has been charged as bidding fee on :auction', ['currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $biddingFee . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>']),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ];

                app(NotificationInterface::class)->create($notificationAttributes);
            }

            if (is_null($checkUserBid))
            {
                $walletAttributes = [
                    [
                        'conditions' => ['user_id' => $bidderWallet->user_id, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'on_order' => ['increment', $parameters['amount']],
                            'balance' => ['decrement', $totalBidAmount],
                        ]
                    ],
                    [
                        'conditions' => ['user_id' => FIXED_USER_SUPER_ADMIN, 'currency_id' => $auction->currency_id],
                        'fields' => [
                            'balance' => ['increment', $biddingFee],
                        ]
                    ],
                ];

                if (!app(WalletInterface::class)->bulkUpdate($walletAttributes)) {
                    throw new Exception('Failed to bid please try again later');
                }

                $transactionAttributes = [
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($parameters['amount'], "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => DECREASED_FROM_USER_WALLET_TO_ESCROW_ON_BIDDING,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($parameters['amount'], "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => INCREASED_TO_ESCROW_ON_BIDDING_FROM_USER_WALLET,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($biddingFee, "-1"),
                        'journal_type' => JOURNAL_TYPE_DEBIT,
                        'journal' => INCREASED_TO_SYSTEM_WALLET_AS_AUCTION_FEES,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],

                    [
                        'user_id' => auth()->id(),
                        'ref_id' => $refId,
                        'wallet_id' => null,
                        'model_id' => null,
                        'model' => null,
                        'amount' => bcmul($biddingFee, "1"),
                        'journal_type' => JOURNAL_TYPE_CREDIT,
                        'journal' => DECREASED_FROM_USER_WALLET_AS_AUCTION_FEES,
                        'updated_at' => $date,
                        'created_at' => $date,
                    ],
                ];
                app(TransactionInterface::class)->insert($transactionAttributes);

                $notificationAttributes = [
                    'user_id' => auth()->id(),
                    'data' => __('Your :currency :amount has been charged from your balance on bidding :auction', ['currency' => '<strong>' . $auction->currency->symbol . '</strong>', 'amount' => '<strong>' . $parameters['amount'] . '</strong>', 'auction' => '<strong>' . $auction->title . '</strong>']),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ];

                app(NotificationInterface::class)->create($notificationAttributes);
            }

            $this->bid->create($parameters);

            DB::commit();

        } catch (Exception $exception) {
            DB::rollBack();

            return [
                SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_ERROR,
                SERVICE_RESPONSE_MESSAGE => $exception->getMessage(),
            ];
        }

        return [
            SERVICE_RESPONSE_STATUS => SERVICE_RESPONSE_SUCCESS,
            SERVICE_RESPONSE_MESSAGE => __('Your bid has been placed successfully'),
        ];
    }

}
