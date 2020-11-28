<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\DisputeInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use App\Services\Core\DataListService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DisputeController extends Controller
{
    private $dispute;

    public function __construct(DisputeInterface $dispute)
    {
        $this->dispute = $dispute;
    }

    public function index($type = null)
    {
        $conditions = [];
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
        $data['routeName'] = 'admin-dispute.index';
        $data['carbon'] = new Carbon();

        return view('backend.dispute.index', $data);
    }

    public function edit($id)
    {
        $data['dispute'] = $this->dispute->findOrFailById($id);
        $data['title'] = __('Dispute Details');

        if ($data['dispute']->dispute_type == DISPUTE_TYPE_AUCTION_ISSUE)
        {
            $data['disputed_link'] = app(AuctionInterface::class)->getFirstByConditions(['ref_id' => $data['dispute']->ref_id]);
        } else
        {
            $data['disputed_link'] = app(SellerInterface::class)->getFirstByConditions(['ref_id' => $data['dispute']->ref_id]);
        }

        return view('backend.dispute.edit', $data);
    }

    public function changeDisputeStatus($id)
    {

        $dispute = $this->dispute->getFirstByConditions(['id' => $id]);
        if (empty($dispute))
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to update status'));
        }
        if ($dispute->dispute_status == DISPUTE_STATUS_PENDING)
        {
            $parameters['dispute_status'] = DISPUTE_STATUS_ON_INVESTIGATION;
        } else {
            $parameters['dispute_status'] = DISPUTE_STATUS_SOLVED;
        }

        if (!$this->dispute->update($parameters, $id))
        {
            return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('Failed to update status'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Status has been updated successfully'));
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
