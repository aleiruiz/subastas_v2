<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\KnowYourCustomerRequest;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\KnowYourCustomerInterface;
use App\Services\Core\FileUploadService;
use App\Services\User\ProfileService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public $knowYourCustomer;

    public function __construct(KnowYourCustomerInterface $knowYourCustomer)
    {
        $this->knowYourCustomer = $knowYourCustomer;
    }

    public function index()
    {
        $data['addressVerifications'] = auth()->user()->knowYourCustomers->where('verification_type', VERIFICATION_TYPE_ADDRESS);
        $data['title'] = __('Verify Your Account');

        return view('frontend.user_access.profile.personal_info.verification.verification_with_address_index', $data);
    }

    public function verificationWithIdIndex()
    {
        $data['idVerification'] = auth()->user()->knowYourCustomers->where('verification_type', VERIFICATION_TYPE_ID)->first();
        $data['title'] = __('Verify Your Account');

        return view('frontend.user_access.profile.personal_info.verification.verification_with_id_index', $data);
    }

    public function createWithId()
    {
        $data['title'] = __('Verify With Id');

        return view('frontend.user_access.profile.personal_info.verification.create_with_id', $data);
    }

    public function createWithAddress()
    {
        $data['title'] = __('Verify With Address');
        $data['addresses'] = Auth::user()->addresses;

        return view('frontend.user_access.profile.personal_info.verification.create_with_address', $data);
    }

    public function verificationWithIdStore(KnowYourCustomerRequest $request)
    {

        try {

            DB::beginTransaction();

            $parameters = $request->only('identification_type');
            $parameters['user_id'] = Auth::user()->id;
            $parameters['status'] = VERIFICATION_STATUS_PENDING;
            $parameters['verification_type'] = VERIFICATION_TYPE_ID;

            if ($request->hasFile('front_image')) {
                $frontImage = app(FileUploadService::class)->upload($request->file('front_image'), config('commonconfig.know_your_customer_images'), 'front_image', 'knowYourCustomer', microtime(), 'public');


                if (!empty($frontImage)) {
                    $parameters['front_image'] = $frontImage;
                }

            }

            if ($request->hasFile('back_image')) {
                $backImage = app(FileUploadService::class)->upload($request->file('back_image'), config('commonconfig.know_your_customer_images'), 'back_image', 'knowYourCustomer', microtime(), 'public');

                if (!empty($backImage)) {
                    $parameters['back_image'] = $backImage;
                }

            }

            $this->knowYourCustomer->create($parameters);

            DB::commit();

            return redirect()->route('profile-verification-with-id.index')->with(SERVICE_RESPONSE_SUCCESS, __('Information has been uploaded successfully'));


        } catch (Exception $exception) {
            DB::rollBack();
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to upload Information'));

    }

    public function verificationWithAddressStore(KnowYourCustomerRequest $request)
    {

        try {

            DB::beginTransaction();

            $parameters = $request->only('address_id','identification_type');
            $parameters['user_id'] = Auth::user()->id;
            $parameters['status'] = VERIFICATION_STATUS_PENDING;
            $parameters['verification_type'] = VERIFICATION_TYPE_ADDRESS;

            if ($request->hasFile('front_image')) {

                $frontImage = app(FileUploadService::class)->upload($request->file('front_image'), config('commonconfig.know_your_customer_images'), 'front_image', 'knowYourCustomer', microtime(), 'public');


                if (!empty($frontImage)) {
                    $parameters['front_image'] = $frontImage;
                }

            }

            $this->knowYourCustomer->create($parameters);

            DB::commit();

            return redirect()->route('profile-verification.index')->with(SERVICE_RESPONSE_SUCCESS, __('Information has been uploaded successfully'));


        } catch (Exception $exception) {
            DB::rollBack();
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to upload Information'));

    }
}
