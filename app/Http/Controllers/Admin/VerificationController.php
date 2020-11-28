<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\User\Interfaces\AddressInterface;
use App\Repositories\User\Interfaces\KnowYourCustomerInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Services\Core\DataListService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationController extends Controller
{
    public $knowYourCustomer;

    public function __construct(KnowYourCustomerInterface $knowYourCustomer)
    {
        $this->knowYourCustomer = $knowYourCustomer;
    }

    public function list($conditions, $routeName)
    {

        $searchFields = [
            ['name', __('name')],
        ];
        $orderFields = [
            ['name', __('name')],
        ];

        $knowYourCustomer = $this->knowYourCustomer->paginateWithFilters($searchFields, $orderFields, $conditions);

        $data['list'] = app(DataListService::class)->dataList($knowYourCustomer, $searchFields, $orderFields);
        $data['customer'] = $this->knowYourCustomer->getByConditions(['verification_type' => VERIFICATION_TYPE_ID]);
        $data['title'] = 'Verification With ID Request';
        $data['routeName'] = $routeName;

        return view('backend.auction.know_your_customer.index', $data);

    }

    public function verificationWithIdIndex($type = null)
    {
        $conditions = [
            'verification_type' => VERIFICATION_TYPE_ID
        ];

        if (!is_null($type)) {
            $conditions['status'] = $type;
        }

        $routeName = 'verification-with-id.index';

        return $this->list($conditions, $routeName);
    }

    public function verificationWithAddressIndex($type = null)
    {
        $conditions = [
            'verification_type' => VERIFICATION_TYPE_ADDRESS
        ];

        if (!is_null($type)) {
            $conditions['status'] = $type;
        }

        $routeName = 'verification-with-address.index';

        return $this->list($conditions, $routeName);
    }

    public function edit($id)
    {
        $data['knowYourCustomer'] = $this->knowYourCustomer->findOrFailById($id);
        $data['addressVerification'] = $data['knowYourCustomer']->where('verification_type', VERIFICATION_TYPE_ADDRESS)->first();

        $data['title'] = __('Verification Review');

        return view('backend.auction.know_your_customer.edit', $data);
    }

    public function changeStatus($id)
    {

        $knowYourCustomer = $this->knowYourCustomer->getFirstById($id);
        $seller = app(SellerInterface::class)->getFirstByConditions(['user_id'=>$knowYourCustomer->user_id]);
        if (empty($knowYourCustomer) || $knowYourCustomer->status != VERIFICATION_STATUS_PENDING) {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to find the request'));
        }

        $parameter['status'] = VERIFICATION_STATUS_APPROVED;
        $sellerStatus['is_active'] = ACTIVE_STATUS_ACTIVE;

        $userAttributes = $knowYourCustomer->verification_type == VERIFICATION_TYPE_ADDRESS ? ['is_address_verified' => VERIFICATION_STATUS_APPROVED] : ['is_id_verified' => VERIFICATION_STATUS_APPROVED];

        $addressAttributes = $knowYourCustomer->verification_type == VERIFICATION_TYPE_ADDRESS ? ['is_verified' => ACTIVE_STATUS_ACTIVE] : null ;

        try {

            DB::beginTransaction();

            $userVerificationStatus = app(UserInterface::class)->update($userAttributes, $knowYourCustomer->user_id);
            $knowYourCustomerStatus = $this->knowYourCustomer->update($parameter, $id);

            if (!$knowYourCustomerStatus || !$userVerificationStatus) {
                throw new \Exception('Failed to approved the verification request.');
            }

            if (!is_null($seller))
            {
                app(SellerInterface::class)->update($sellerStatus, $seller->id);
            }

            if (!is_null($addressAttributes))
            {
                app(AddressInterface::class)->update($addressAttributes, $knowYourCustomer->address->id);
            }

            DB::commit();

            return redirect()->route('verification-with-id.index')->with(SERVICE_RESPONSE_SUCCESS, __('The Request has been Approved'));


        } catch (Exception $exception) {
            DB::rollback();
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, $exception->getMessage());
        }

    }

    public function destroy($id)
    {
        if ($this->knowYourCustomer->deleteById($id)) {
            return redirect()->route('verification-with-id.index')->with(SERVICE_RESPONSE_SUCCESS, __('Request has been Declined'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, 'Unable to Decline the request ');
    }

}
