<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\DisputeRequest;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\DisputeInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Services\Core\DataListService;
use App\Services\Core\FileUploadService;
use Carbon\Carbon;

class DisputeController extends Controller
{
    private $dispute;

    public function __construct(DisputeInterface $dispute)
    {
        $this->dispute = $dispute;
    }

    public function index($type = null)
    {
        $conditions = [
            'user_id' => auth()->user()->id,
        ];
        if (!is_null($type)) {
            $conditions['dispute_type'] = $type;
        }

        $searchFields = [
            ['title', __('Dispute Title')],
            ['report_type', __('Dispute Type')],
            ['description', __('Description')],
        ];
        $orderFields = [
            ['report_type', __('Dispute Type')],
            ['title', __('Title')],
            ['id', __('Id')],
        ];

        $filters = [
            ['disputes.report_type', __('Auction Type'), dispute_type()],
        ];

        $query = $this->dispute->paginateWithFilters($searchFields, $orderFields, $conditions, $filters);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields, $filters);
        $data['title'] = 'All Reports';
        $data['routeName'] = 'dispute.index';
        $data['carbon'] = new Carbon();

        return view('frontend.user_access.dispute.index', $data);
    }

    public function create($disputeType = null, $refId = null)
    {
        $data['disputeType'] = $disputeType;
        $data['refId'] = $refId;
        $data['title'] = __('Create Report');

        return view('frontend.user_access.dispute._form_dispute', $data);
    }

    public function specific($disputeType, $refId)
    {
        return $this->create($disputeType, $refId);

    }

    public function store(DisputeRequest $request)
    {

        $parameters = $request->only('title', 'dispute_type', 'description');
        $parameters['user_id'] = auth()->id();
        $parameters['dispute_status'] = DISPUTE_STATUS_PENDING;

        if (in_array($parameters['dispute_type'], [DISPUTE_TYPE_AUCTION_ISSUE, DISPUTE_TYPE_SELLER_ISSUE, DISPUTE_TYPE_TRANSACTION_ISSUE, DISPUTE_TYPE_SHIPPING_ISSUE])) {

            if ($parameters['dispute_type'] == DISPUTE_TYPE_AUCTION_ISSUE) {
                $data = app(AuctionInterface::class)->getFirstByConditions(['ref_id' => $request->ref_id]);
                if (!$data){
                    return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Reference ID does not match'));
                }
            } elseif ($parameters['dispute_type'] == DISPUTE_TYPE_SELLER_ISSUE) {
                $data = app(SellerInterface::class)->getFirstByConditions(['ref_id' => $request->ref_id]);
                if (!$data){
                    return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Reference ID does not match'));
                }
            } elseif ($parameters['dispute_type'] == DISPUTE_TYPE_SHIPPING_ISSUE) {
                $data = app(AuctionInterface::class)->getFirstByConditions(['ref_id' => $request->ref_id]);
                $para['product_claim_status'] = AUCTION_PRODUCT_CLAIM_STATUS_DISPUTED;
                $updateStatus = app(AuctionInterface::class)->update($para, $data->id);
                if (!$data && !$updateStatus){
                    return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Reference ID does not match'));
                }
            } else {
                $data = app(TransactionInterface::class)->getFirstByConditions(['ref_id' => $request->ref_id]);
                if (!$data){
                    return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Reference ID does not match'));
                }
            }
            if (!$data) {
                return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed To Dispute.'));
            }
            $parameters['ref_id'] = $data->ref_id;
            $parameters['model'] = get_class($data);
        }

        $new_name = 0;
        if ($request->hasfile('images')) {
            $uploadedImage = [];
            foreach ($request->images as $files) {
                $uploadedImage[] = app(FileUploadService::class)->upload($files, config('commonconfig.dispute_image'), 'images', '', $new_name++, 'public', 400, 400);
            }

            if (!empty($uploadedImage)) {
                $parameters['images'] = $uploadedImage;
            }
        }

        if ($this->dispute->create($parameters)) {

            return redirect()->route('home')->with(SERVICE_RESPONSE_SUCCESS, __('Dispute has been submitted.'));

        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed To Dispute.'));

    }

    public function markAsRead($id)
    {
        if ($this->dispute->read($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('This Dispute has been marked as read.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to mark as read.'));
    }

    public function markAsUnread($id)
    {
        if ($this->dispute->unread($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('This Dispute has been marked as unread.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to mark as unread.'));
    }

}
