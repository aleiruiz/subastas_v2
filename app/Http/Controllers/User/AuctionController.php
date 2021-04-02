<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AuctionRequest;
use App\Http\Requests\User\ShippingDescriptionRequest;
use App\Jobs\AuctionMoneyRelease;
use App\Models\User\User;
use App\Models\User\WarrantyUserAuction;
use App\Repositories\Admin\Interfaces\CategoryInterface;
use App\Repositories\Admin\Interfaces\CurrencyInterface;
use App\Repositories\Core\Interfaces\CountryInterface;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\KnowYourCustomerInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Services\Admin\AuctionService;
use App\Services\Core\FileUploadService;
use App\Services\User\ReleaseAuctionMoneyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

use Illuminate\Support\Facades\DB;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Http\Requests\User\BidRequest;
use App\Services\User\BidService;

class AuctionController extends Controller
{
    private $auction;

    public function __construct(AuctionInterface $auction)
    {
        $this->auction = $auction;
    }

    public function create()
    {

        $seller = Auth::user()->seller;

        if (empty($seller)) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Access Denied'));
        }

        $data['seller'] = $seller;
        $data['isAddressVerified'] = app(KnowYourCustomerInterface::class)->getFirstByConditions(['user_id' => $seller->user_id, 'status' => ACTIVE_STATUS_ACTIVE]);
        $data['categories'] = app(CategoryInterface::class)->getAll()->pluck('name', 'id');
        $data['currencies'] = app(CurrencyInterface::class)->getByConditions(['is_active' => ACTIVE_STATUS_ACTIVE])->pluck('name', 'id');
        $data['title'] = __('Create Auction');

        return view('frontend.user_access.auction.create', $data);
    }

    public function store(AuctionRequest $request)
    {
        $seller = Auth::user()->seller;

        if (empty($seller)) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Access Denied'));
        }

        $parameters = $request->only('title', 'address_id', 'auction_type', 'category_id', 'currency_id', 'product_description', 'starting_date', 'ending_date', 'bid_initial_price', 'bid_increment_dif', 'is_shippable', 'shipping_type', 'terms_description', 'is_multiple_bid_allowed', 'reserve_price');
        $parameters['ref_id'] = Str::uuid();
        $parameters['ending_date'] = Carbon::parse($parameters['starting_date'])->addSeconds(7200);
        $parameters['seller_id'] = Auth::user()->seller->id;

        $new_name = 0;
        if ($request->hasfile('images')) {
            $uploadedImage = [];
            foreach ($request->images as $files) {
                $uploadedImage[] = app(FileUploadService::class)->upload($files, config('commonconfig.auction_image'), bin2hex(random_bytes(10)), '', $new_name++, 'public', 600, 400);
            }

            if (!empty($uploadedImage)) {
                $parameters['images'] = $uploadedImage;
            }
        }

        $auction = $this->auction->create($parameters);

        if ($auction) {
            return redirect()->route('auction.show', $auction->id)->with(SERVICE_RESPONSE_SUCCESS, __('Auction has been created successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create Auction'));
    }

    public function show($id)
    {
        $auction_ = app(AuctionService::class)->auctionDetails($id);
        //dd($auction_['auction']->warranty->where('user_id', 2)->first());
        $lst = false;
        if ($auction_['auction']->status == AUCTION_STATUS_RUNNING) {
            $last_bid = app(BidInterface::class)->getFirstByConditions(['auction_id' => $id], null, ['id' =>'desc']);
            if (!is_null($last_bid)) {
                $time_ = Carbon::parse($last_bid->created_at)
                            ->addSeconds(((TIME_INTERVAL_AUCTION / 1000)) * 3)
                            ->format('H:i:s');
                if ($time_ < now()->format('H:i:s')) {
                    $lst = ($last_bid->amount < $auction_['auction']->reserve_price) ? false : true;
                    $parameter['is_winner'] = AUCTION_WINNER_STATUS_WIN;
                    $changeAuctionStatus['status'] = AUCTION_STATUS_COMPLETED;
                    $changeAuctionStatus['ending_date'] = \Carbon\Carbon::now()->addMinutes(-3);

                    $updateAsWinner = app(BidInterface::class)->update($parameter, $last_bid->id);
                    $completeAuction = app(AuctionInterface::class)->update($changeAuctionStatus, $id);

                    if ($updateAsWinner && $completeAuction && $last_bid->user_id == auth()->user()->id) {
                        $date = now();
                        $route = route('shipping-description.create', ['id' => $id]);
                        $notificationAttributes = [
                            'user_id' => $updateAsWinner->user_id,
                            'data' => __('You just won the :auction, please submit your address', ['auction' => '<strong>' . $auction_['auction']->title . '</strong>']),
                            'link' => $route,
                            'updated_at' => $date,
                            'created_at' => $date,
                        ];

                        app(NotificationInterface::class)->insert($notificationAttributes);
                    }
                }
            }
        }
        //dd($auction_['isWinner']->user);
        $details = app(AuctionService::class)->auctionDetails($id);
        $details['price_reserve'] = $lst;
        //dd($details);
        return view('frontend.user_access.auction.show', $details);
    }

    public function updateShippingStatusFromSeller(Request $request, $id)
    {
        $auction = $this->auction->getFirstByConditions(['id' => $id]);
        if ($auction->status != AUCTION_STATUS_COMPLETED && $auction->seller_id != auth()->user()->seller->id) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to Submit'));
        }

        $parameters['delivery_date'] = Carbon::parse($request->input('delivery_date'));

        $parameters['product_claim_status'] = AUCTION_PRODUCT_CLAIM_STATUS_ON_SHIPPING;
        $updatedShippingStatus = $this->auction->update($parameters, $id);

        $isWinner = app(BidInterface::class)->getFirstByConditions(['auction_id' => $id, 'is_winner' => AUCTION_WINNER_STATUS_WIN]);
        $date = now();
        $route = route('shipping-description.create', ['id' => $auction->id]);
        $notificationAttributes = [
            'user_id' => $isWinner->user_id,
            'data' => __(' Your product of :auction auction is on shipping', ['auction' => '<strong>' . $auction->title . '</strong>']),
            'link' => $route,
            'updated_at' => $date,
            'created_at' => $date,
        ];

        $winnerNotificationOnProductShipping = app(NotificationInterface::class)->create($notificationAttributes);

        if (!empty($updatedShippingStatus && !empty($winnerNotificationOnProductShipping))) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Request has been submitted successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to submit the request'));
    }

    public function updateShippingStatusFromUser($id)
    {
        $auction = $this->auction->findOrFailById($id);

        $isWinner = app(BidInterface::class)->getFirstByConditions(['auction_id' => $id, 'is_winner' => AUCTION_WINNER_STATUS_WIN]);

        if ($auction->status != AUCTION_STATUS_COMPLETED && $isWinner->user_id != auth()->user()->id) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Submission has been failed'));
        }

        $releaseMoney = app(ReleaseAuctionMoneyService::class)->releaseAuctionMoney($id);
        if (!$releaseMoney)
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to submit'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Submission has been successful.'));
    }

    public function shippingDescriptionCreate($id)
    {

        $data['auction'] = $this->auction->findOrFailById($id);
        $data['isWinner'] = app(BidInterface::class)->getFirstByConditions(['auction_id' => $id, 'is_winner' => AUCTION_WINNER_STATUS_WIN]);

        if (is_null($data['isWinner']) || $data['isWinner']->user_id != auth()->user()->id) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Access Denied'));
        }


        $submittedDay = Carbon::parse($data['auction']->delivery_date);
        $disputeDays = settings('dispute_time');
        $data['reportWithIn'] = $submittedDay->addDays($disputeDays);

        $data['carbon'] = new Carbon();
        $data['addresses'] = auth()->user()->addresses;
        $data['productReceivingAddress'] = app(AddressInterface::class)->getFirstByConditions(['id' => $data['auction']->address_id]);
        $data['countries'] = app(CountryInterface::class)->getAll()->pluck('name', 'id')->toArray();
        $data['title'] = __('Shipping Description');

        return view('frontend.user_access.auction.shipping_description', $data);

    }

    public function shippingDescriptionUpdate(ShippingDescriptionRequest $request, $id)
    {
        $auction = $this->auction->getFirstByConditions(['id' => $id]);
        $isWinner = app(BidInterface::class)->getFirstByConditions(['auction_id' => $id, 'is_winner' => AUCTION_WINNER_STATUS_WIN]);
        if ($auction->status != AUCTION_STATUS_COMPLETED && $isWinner->user_id !== auth()->user()->id) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to Submit'));
        }

        if ($request->address_id == 0) {
            $addressParameters = $request->only('name', 'address', 'phone_number', 'post_code', 'city', 'country_id', 'state_id');
            $addressParameters['ownerable_type'] = get_class(new User());
            $addressParameters['ownerable_id'] = Auth::user()->id;
            $addressParameters['is_verified'] = VERIFICATION_STATUS_UNVERIFIED;

            $newAddress = app(AddressInterface::class)->create($addressParameters);
        }

        $parameters = [
            'shipping_description' => $request->shipping_description,
        ];

        if (!empty($newAddress)) {
            $parameters = [
                'address_id' => $newAddress->id,
            ];
        } else {
            $parameters = [
                'address_id' => $request->address_id,
            ];
        }

        $shippingDescription = $this->auction->update($parameters, $id);
        $date = now();

        $route = route('auction.show', ['id' => $auction->id]);
        $notificationAttributes = [
            'user_id' => $auction->seller->user->id,
            'data' => __('Winner of :auction auction :winner has submitted the shipping address', ['auction' => '<strong>' . $auction->title . '</strong>', 'winner' => '<strong>' . $auction->title . '</strong>']),
            'link' => $route,
            'updated_at' => $date,
            'created_at' => $date
        ];

        $sellerNotification = app(NotificationInterface::class)->create($notificationAttributes);

        if (!empty($shippingDescription) && !empty($sellerNotification)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Your description has been submitted successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to submit your description'));

    }

    public function add_count_visits(Request $request){
        $auction_id = $request['auction_id'];

        $details = app(AuctionService::class)->auctionDetails($auction_id);
        $visits = $details['auction']->countvisits;

        $changeAuctionStatus['countvisits'] = $visits + 1;
        $completeAuction = app(AuctionInterface::class)->update($changeAuctionStatus, $auction_id);

        return response()->json(true);
    }
    
    public function bid_list($id){
        //dd($id);

        $bids = app(BidInterface::class)->getByConditions(['auction_id' => $id]);

         $cont = 1;
         $html = '';
         $bids = $bids->sortByDesc('amount');
        foreach ($bids->groupBy('user_id') as $item) {
            $html .= '<tr>
                        <td>' . $cont . '</td>
                        <td>' . $item[0]->user->username . '</td>
                        <td>' . $item->count() . '</td>
                    </tr>';
            $cont = $cont + 1;
        }
        $data['html'] = $html;
        return view('frontend.user_access.auction.lists_bid', $data);
    }
    
    public function pay_warranty(Request $request){
        
        $warranty = WarrantyUserAuction::where('user_id', Auth::user()->id)
                            ->where('auction_id', $request['id'])->count();
        //dd($warranty);
        if ($warranty == 0) {
            $conditions = [
                'id' => $request['id']
            ];

            $auction = $this->auction->getFirstByConditions($conditions);
            $onBiddingUserWallet = app(WalletInterface::class)->getFirstByConditions(['user_id' => auth()->id(), 'currency_id' => $auction->currency_id], 'currency');
        
            $highestBidderBiddingFee = settings('bidding_fee_on_highest_bidder_auction');
            $vickreyBidderBiddingFee = settings('bidding_fee_on_vickrey_bidder_auction');

            if ($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER) {
                $biddingFee = $highestBidderBiddingFee;
            } else {
                $biddingFee = $vickreyBidderBiddingFee;
            }

            $bidInsurance = bcadd($auction->bid_initial_price * 0.10, $biddingFee);
            $highestBid = $auction->bids()->orderBy('amount', 'desc')->first();

        
            $walletAttributes = [
                [
                    'conditions' => ['user_id' => $onBiddingUserWallet->user_id, 'currency_id' => $auction->currency_id],
                    'fields' => [
                        'on_order' => ['increment', '10'],
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
            //dd($walletAttributes);
            DB::beginTransaction();
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

            $parameters['ending_date'] = \Carbon\Carbon::now()->addSeconds((TIME_INTERVAL_AUCTION / 1000) * 3);
            $timeUpdate = app(AuctionInterface::class)->update($parameters, $auction->id);
        
            app(TransactionInterface::class)->insert($transactionAttributes);

            DB::commit();

            $paramWarranty['user_id'] = Auth::user()->id;
            $paramWarranty['auction_id'] = $request['id'];
            $paramWarranty['amount'] = $bidInsurance;
            WarrantyUserAuction::create($paramWarranty);
        }
        return json_encode(true);
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
}
