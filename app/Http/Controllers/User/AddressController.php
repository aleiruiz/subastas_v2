<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\AddressRequest;
use App\Models\User\Seller;
use App\Repositories\Core\Interfaces\CountryInterface;
use App\Repositories\User\Interfaces\AddressInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    private $address;

    public function __construct(AddressInterface $address)
    {
        $this->address = $address;
    }

    public function Index()
    {

        $data['title'] = __('Store Address');
        $data['addresses'] = auth()->user()->seller->address;

        return view('frontend.user_access.profile.stores.manage_store.address.index', $data);
    }

    public function getStateByCountry(Request $request)
    {
        $states = [];

        if($request->has('country_id'))
        {
            $country = app(CountryInterface::class)->getFirstById($request->country_id, ['states']);

            $states = $country->states->pluck('name', 'id')->toArray();
        }

        return response()->json(['states' => $states]);
    }

    public function create()
    {
        $data['title'] = __('Create New Address');
        if (auth()->user()->seller){
            $sellerClass = get_class(auth()->user()->seller);
            $data['addresses'] = app(AddressInterface::class)->getByConditions(['ownerable_type' => $sellerClass,'ownerable_id' => Auth::user()->seller->id]);
        }
        $data['countries'] = app(CountryInterface::class)->getAll()->pluck('name', 'id')->toArray();

        return view('frontend.user_access.profile.stores.manage_store.address.create', $data);
    }

    public function store(AddressRequest $request)
    {

        $checkAddressStatus = $this->address->getFirstByConditions(['ownerable_id' => Auth::user()->seller->id,'is_default' => ACTIVE_STATUS_ACTIVE]);

        $activeStatus = ACTIVE_STATUS_INACTIVE;
        if (is_null($checkAddressStatus))
        {
            $activeStatus = ACTIVE_STATUS_ACTIVE;
        }

        $parameters = $request->only('name', 'address', 'phone_number', 'post_code', 'city', 'country_id', 'state_id');
        $parameters['ownerable_type'] = get_class(new Seller());
        $parameters['ownerable_id'] = Auth::user()->seller->id;
        $parameters['is_verified'] = VERIFICATION_STATUS_UNVERIFIED;
        $parameters['is_default'] = $activeStatus;

        if ($this->address->create($parameters))
        {
            return redirect()->route('address.index')->with(SERVICE_RESPONSE_SUCCESS, __('Address has been created successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to Create Address'));

    }

    public function edit($id)
    {
        $data['address'] = $this->address->findOrFailById($id);
        $data['addresses'] = app(AddressInterface::class)->getByConditions(['ownerable_id' => Auth::user()->seller->id]);
        $data['countries'] = app(CountryInterface::class)->getAll()->pluck('name', 'id')->toArray();
        $data['title'] = __('Edit Address Information');

        return view('frontend.user_access.profile.stores.manage_store.address.edit', $data);
    }

    public function update(AddressRequest $request, $id)
    {
        $parameters = $request->only('name', 'address', 'phone_number', 'post_code', 'city', 'country_id', 'state_id');
        $parameters['ownerable_type'] = get_class(new Seller());
        $parameters['ownerable_id'] = Auth::user()->seller->id;
        $parameters['is_verified'] = VERIFICATION_STATUS_UNVERIFIED;
        $parameters['is_default'] = ACTIVE_STATUS_INACTIVE;

        if ($this->address->update($parameters, $id))
        {
            return redirect()->route('address.index')->with(SERVICE_RESPONSE_SUCCESS, __('Address has been updated successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to update Address'));
    }

    public function makeAddressDefault(Request $request, $id)
    {
        $parameters = $request->only('is_default');
        $conditions = [
            $parameters['ownerable_id'] = Auth::user()->seller->id,
            $parameters['is_verified'] = VERIFICATION_STATUS_APPROVED,
            $parameters['is_default'] = ACTIVE_STATUS_INACTIVE,
        ];

        $address = $this->address->getFirstByConditions($conditions);
        $verifiedAddress = $this->address->getFirstByConditions(['ownerable_id' => Auth::user()->seller->id,'is_default' => ACTIVE_STATUS_ACTIVE ]);

        if (!empty($address) && !empty($verifiedAddress))
        {
            $activeAddressAttribute = ['is_default' => ACTIVE_STATUS_ACTIVE];
            $inActiveAddressAttribute = ['is_default' => ACTIVE_STATUS_INACTIVE];

            $updateActiveAddressAttribute = $this->address->update($activeAddressAttribute, $id);
            $updateInactiveAddressAttribute = $this->address->update($inActiveAddressAttribute, $verifiedAddress->id);

            if ($updateActiveAddressAttribute && $updateInactiveAddressAttribute)
            {
                return redirect()->route('address.index')->with(SERVICE_RESPONSE_SUCCESS, __('Successful'));
            }

            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Could not able to make this address Default'));

        }

        if (!empty($address))
        {
            $activeAddressAttribute = ['is_default' => ACTIVE_STATUS_ACTIVE];
            $updateActiveAddressAttribute = $this->address->update($activeAddressAttribute, $id);

            if ($updateActiveAddressAttribute)
            {
                return redirect()->route('address.index')->with(SERVICE_RESPONSE_SUCCESS, __('Successful'));
            }

            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Could not able to make this address Default'));

        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Could not able to make this address Default'));
    }

    public function destroy($id)
    {

        if ($this->address->deleteById($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Address has been deleted successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This Address can not be deleted.'));
    }

}
