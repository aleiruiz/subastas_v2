<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\KnowYourCustomerRequest;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\KnowYourCustomerInterface;
use App\Services\Core\FileUploadService;
use App\Services\User\ProfileService;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SellerVerificationController extends Controller
{
    public $knowYourCustomer;

    public function __construct(KnowYourCustomerInterface $knowYourCustomer)
    {
        $this->knowYourCustomer = $knowYourCustomer;
    }

    public function index($type = null)
    {
        $data['addressVerifications'] = $this->knowYourCustomer->getByConditions(['verification_type' => VERIFICATION_TYPE_ADDRESS, 'user_id' => auth()->user()->seller->user_id]);

        $data['title'] = __('Verify Your Account');

        return view('frontend.user_access.profile.stores.verification.index', $data);
    }

    public function createWithAddress()
    {
        $data['title'] = __('Verify With Address');
        $data['addresses'] = auth()->user()->seller->address->where('is_verified', ACTIVE_STATUS_INACTIVE);

        return view('frontend.user_access.profile.stores.verification.create_with_address', $data);
    }

    public function verificationWithAddressStore(KnowYourCustomerRequest $request)
    {

        try {

            DB::beginTransaction();

            $parameters = $request->only('address_id','identification_type');
            $parameters['user_id'] = Auth::user()->id;
            $parameters['status'] = VERIFICATION_STATUS_PENDING;
            $parameters['verification_type'] = VERIFICATION_TYPE_ADDRESS;

            if ($request->hasFile('front_image') && $request->has('address_id')) {

                $frontImage = app(FileUploadService::class)->upload($request->file('front_image'), config('commonconfig.know_your_customer_images'), 'front_image', 'knowYourCustomer', microtime(), 'public');


                if (!empty($frontImage)) {
                    $parameters['front_image'] = $frontImage;
                }

            }

            $this->knowYourCustomer->create($parameters);

            DB::commit();

            return redirect()->route('seller-verification.index')->with(SERVICE_RESPONSE_SUCCESS, __('Information has been uploaded successfully'));


        } catch (Exception $exception) {
            DB::rollBack();
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to upload Information'));

    }
}
