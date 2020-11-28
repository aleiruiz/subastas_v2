<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Services\Core\DataListService;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    private $userNotifications;

    public function __construct(NotificationInterface $userNotifications)
    {
        $this->userNotifications = $userNotifications;
    }

    public function index()
    {
        $condition = [
            'user_id' => auth()->user()->id
        ];

        $searchFields = [
            ['data', __('Details')],
        ];
        $orderFields = [
            ['user_id', __('User')],
            ['created_at', __('Created')],
        ];

        $query = $this->userNotifications->paginateWithFilters($searchFields, $orderFields, $condition);
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields);
        $data['title'] = 'My Notifications';

        return view('frontend.user_access.notification.index', $data);
    }

    public function markAsRead($id)
    {
        if($this->userNotifications->read($id)){
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS,__('The notice has been marked as read.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR,__('Failed to mark as read.'));
    }

    public function markAsUnread($id)
    {
        if($this->userNotifications->unread($id)){
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS,__('The notice has been marked as unread.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR,__('Failed to mark as unread.'));
    }
}
