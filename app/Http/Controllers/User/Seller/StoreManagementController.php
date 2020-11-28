<?php

namespace App\Http\Controllers\User\Seller;

use App\Http\Controllers\Controller;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use Illuminate\Support\Facades\Auth;

class StoreManagementController extends Controller
{
    private $seller;

    public function __construct(SellerInterface $seller)
    {
        $this->seller = $seller;
    }

    public function index()
    {
        $data['title'] = __('Store Management');
        $data['addresses'] = app(AddressInterface::class)->getByConditions(['ownerable_id' => Auth::user()->seller->id]);

        return view('frontend.user_access.profile.stores.manage_store.index', $data);
    }


}
