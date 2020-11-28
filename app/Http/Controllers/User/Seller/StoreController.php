<?php

namespace App\Http\Controllers\User\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\SellerProfileRequest;
use App\Models\User\Address;
use App\Models\User\Seller;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Services\Core\DataListService;
use App\Services\Core\FileUploadService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    private $seller;

    public function __construct(SellerInterface $seller)
    {
        $this->seller = $seller;
    }

    public function list($conditions, $routeName)
    {
        $searchFields = [
            ['title', __('Title')],
            ['auction_type', __('Auction Type')],
        ];

        $orderFields = [
            ['title', __('Title')],
        ];

        $data['title'] = __('Profile');
        $data['routeName'] = $routeName;
        $data['carbon'] = Carbon::parse();
        $data['today'] = Carbon::now();
        $data['defaultAddress'] = app(AddressInterface::class)->sellerDefaultAddress();

        $auctions = app(AuctionInterface::class)->paginateWithFilters($searchFields, $orderFields, $conditions);
        $data['list'] = app(DataListService::class)->dataList($auctions, $searchFields, $orderFields);

        return view('frontend.user_access.profile.stores.index', $data);
    }


    public function sellerIndex($status=null)
    {
        $conditions = [
            'seller_id' => auth()->user()->seller->id
        ];

        if (!is_null($status)) {
            $conditions['status'] = $status;
        }

        $routeName = 'seller-profile.index';

        return $this->list($conditions, $routeName);
    }

    public function create()
    {
        $data['title'] = __('Create a store');
        $data['addresses'] = app(AddressInterface::class)->getByConditions(['ownerable_id' => Auth::user()->id]);

        return view('frontend.user_access.profile.stores.create', $data);
    }

    public function store(SellerProfileRequest $request)
    {

        $parameters = $request->only('name', 'description', 'phone_number', 'email');
        $parameters['user_id'] = auth()->user()->id;
        $parameters['ref_id'] = generate_id();

        if ($request->hasFile('image'))
        {
            $image = app(FileUploadService::class)->upload($request->file('image'), config('commonconfig.seller_profile_images'), 'image', 'seller', microtime(), 'public');

            if (!empty($image)) {
                $parameters['image'] = $image;
            }

        }

        $userAttributes['role_id'] = USER_ROLE_SELLER;
        $createdSeller = $this->seller->create($parameters);
        $userVerificationStatus = app(UserInterface::class)->update($userAttributes, auth()->user()->id);

        if ($createdSeller && $userVerificationStatus)
        {

            return redirect()->route('seller-profile.index')->with(SERVICE_RESPONSE_SUCCESS, __('Store has been created Successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create the store'));

    }

    public function show($id, $status=null)
    {
        $conditions = [
            'seller_id' => $id
        ];

        if (!is_null($status)) {
            $conditions['status'] = $status;
        }

        $routeName = 'seller-profile.show';

        $searchFields = [
            ['title', __('Title')],
            ['auction_type', __('Auction Type')],
        ];

        $orderFields = [
            ['title', __('Title')],
        ];


        $data['title'] = __('Seller Profile');
        $data['routeName'] = $routeName;
        $data['seller'] = app(SellerInterface::class)->findOrFailById($id);

        $sellerClass = get_class($data['seller']);
        $data['isAddressVerified'] = app(AddressInterface::class)->getFirstByConditions(['ownerable_id' => $id, 'ownerable_type' => $sellerClass]);
        $auctions = app(AuctionInterface::class)->paginateWithFilters($searchFields, $orderFields, $conditions);

        $data['list'] = app(DataListService::class)->dataList($auctions, $searchFields, $orderFields);


        return view('frontend.user_access.profile.stores.show', $data);
    }

    public function edit($id)
    {
        $checkSeller = $this->seller->getFirstByConditions(['id' => $id]);
        if ($checkSeller->user_id != auth()->user()->seller->id)
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Access Denied'));
        }
        $data['seller'] = $this->seller->findOrFailById($id);
        $data['addresses'] = app(AddressInterface::class)->getByConditions(['ownerable_id' => Auth::user()->seller->id]);
        $data['title'] = __('Edit Store Information');

        return view('frontend.user_access.profile.stores.manage_store.edit', $data);
    }

    public function update(SellerProfileRequest $request, $id)
    {
        $parameters = $request->only('name', 'description', 'phone_number', 'email');
        $parameters['user_id'] = auth()->user()->id;

        if ($request->hasFile('image'))
        {
            $image = app(FileUploadService::class)->upload($request->file('image'), config('commonconfig.seller_profile_images'), 'image', 'seller', microtime(), 'public');

            if (!empty($image)) {
                $parameters['image'] = $image;
            }

        }

        if ($this->seller->update($parameters, $id))
        {
            return redirect()->route('seller-profile.index')->with(SERVICE_RESPONSE_SUCCESS, __('Store has been created Successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to create the store'));
    }


}
