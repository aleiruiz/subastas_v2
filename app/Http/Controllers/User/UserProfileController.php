<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Core\PasswordUpdateRequest;
use App\Http\Requests\User\UserAvatarRequest;
use App\Http\Requests\User\UserRequest;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\BidInterface;
use App\Repositories\User\Interfaces\ProfileInterface;
use App\Services\Core\DataListService;
use App\Services\User\ProfileService;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    private $service;

    public function __construct(ProfileService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $data = $this->service->profile();
        $data['title'] = __('Profile');

        return view('frontend.user_access.profile.personal_info.index', $data);
    }

    public function userIndex()
    {
        $conditions = [
            'user_id' => auth()->id()
        ];

        return $this->list($conditions);
    }

    public function list($conditions)
    {
        $searchFields = [
            ['title', __('Title')],
            ['auction_type', __('Auction Type')],
        ];

        $orderFields = [
            ['title', __('Title')],
        ];

        $joinArray = [
          ['bids', 'bids.auction_id', '=', 'auctions.id']
        ];

        $data['title'] = __('Profile');
        $data['defaultAddress'] = Auth()->user()->addresses()->where('is_default', ACTIVE_STATUS_ACTIVE)->first();
        $auctions = app(AuctionInterface::class)->paginateWithFilters($searchFields, $orderFields, $conditions, null, null, $joinArray );

        $data['list'] = app(DataListService::class)->dataList($auctions, $searchFields, $orderFields);

        return view('frontend.user_access.profile.index', $data);
    }

    public function userWonAuctionIndex()
    {

        $conditions = [
            'is_winner' => AUCTION_WINNER_STATUS_WIN,
            'user_id' => auth()->user()->id,
            'status' => AUCTION_STATUS_COMPLETED,
        ];

        return $this->list($conditions);
    }

    public function edit()
    {
        $data = $this->service->profile();
        $data['title'] = __('Edit Profile');

        return view('frontend.user_access.profile.personal_info.edit', $data);
    }

    public function update(UserRequest $request, ProfileInterface $profile)
    {
        $parameters = $request->only(['first_name', 'last_name', 'address']);
        if ($profile->update($parameters, Auth::id(), 'user_id')) {
            return redirect()->route('user-profile.edit')->with(SERVICE_RESPONSE_SUCCESS, __('Profile has been updated successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to update profile.'));
    }

    public function changePassword()
    {
        $data = $this->service->profile();
        $data['title'] = __('Change Password');

        return view('frontend.user_access.profile.personal_info.change_password', $data);
    }

    public function updatePassword(PasswordUpdateRequest $request)
    {
        $response = $this->service->updatePassword($request);
        $status = $response[SERVICE_RESPONSE_STATUS] ? SERVICE_RESPONSE_SUCCESS : SERVICE_RESPONSE_ERROR;

        return redirect()->back()->with($status, $response[SERVICE_RESPONSE_MESSAGE]);
    }

    public function setting()
    {
        $data = $this->service->profile();
        $data['title'] = __('Setting');

        return view('backend.profile.setting', $data);
    }

    public function settingEdit()
    {
        $data = $this->service->profile();
        $data['title'] = __('Edit Setting');

        return view('backend.profile.setting_edit_form', $data);
    }

    public function settingUpdate(UserSettingRequest $request, UserSettingInterface $userSetting)
    {
        $parameters = [
            'language' => $request->get('language', config('app.locale')),
            'timezone' => $request->get('timezone', config('app.timezone')),
        ];

        if ($userSetting->update($parameters, Auth::id(), 'user_id')) {
            return redirect()->route('profile.setting.edit')->with(SERVICE_RESPONSE_SUCCESS, __('User setting has been updated successfully.'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_SUCCESS, __('User setting has been updated successfully.'));
    }

    public function avatarEdit()
    {
        $data = $this->service->profile();
        $data['title'] = __('Change Avatar');

        return view('frontend.user_access.profile.personal_info.avatar_edit_form', $data);
    }

    public function avatarUpdate(UserAvatarRequest $request)
    {
        $response = $this->service->avatarUpload($request);
        $status = $response[SERVICE_RESPONSE_STATUS] ? SERVICE_RESPONSE_SUCCESS : SERVICE_RESPONSE_ERROR;

        return redirect()->back()->with($status, $response[SERVICE_RESPONSE_MESSAGE]);
    }

}
