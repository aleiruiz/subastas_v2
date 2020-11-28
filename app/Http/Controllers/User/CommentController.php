<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CommentRequest;
use App\Repositories\User\Interfaces\AuctionInterface;
use App\Repositories\User\Interfaces\CommentInterface;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\SellerInterface;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    private $comment;

    public function __construct(CommentInterface $comment)
    {
        $this->comment = $comment;
    }

    public function store(CommentRequest $request, $auctionId)
    {

        $IsUserIdVerified = auth()->user()->is_id_verified;
        $IsUserAddressVerified = auth()->user()->is_address_verified;

        if ($IsUserIdVerified != ACTIVE_STATUS_ACTIVE || $IsUserAddressVerified != ACTIVE_STATUS_ACTIVE)
        {
            return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Sorry you can not comment here, verify your account first'));
        }

        $parameters = $request->only('content');
        $parameters['user_id'] = auth()->user()->id;
        $parameters['auction_id'] = $auctionId;

        $comment = $this->comment->create($parameters);

        $userName = auth()->user()->username;
        $auction = app(AuctionInterface::class)->getFirstByConditions(['id' => $auctionId]);
        $auctionCreator = app(SellerInterface::class)->getFirstByConditions(['id' => $auction->seller_id]);
        $route = route('auction.show',['id' => $auctionId]);
        $link = '<a href="'. $route .'">' . __('View Comment') . '</a>';

        $commentNotifications = [
            'user_id' => $auctionCreator->id,
            'data' => __("<strong>:username</strong> commented on your auction <strong>:auction</strong> :link", ['username' => $userName,'auction' => $auction->title,'link' => $link])
        ];

        $notification = app(NotificationInterface::class)->create($commentNotifications);

        if (!empty($comment) && !empty($notification) )
        {
            return redirect()->route('auction.show', $auctionId)->with(SERVICE_RESPONSE_SUCCESS, __('Comment has been submitted successfully'));
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to submit the comment'));

    }

    public function edit($id)
    {
        $data = $this->comment->findOrFailById($id);
        $data['title'] = __('Edit Comment');

        return view('frontend.user_access.auction.show', $data);
    }


    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        if ($this->comment->deleteById($id)) {
            return redirect()->back()->with(SERVICE_RESPONSE_SUCCESS, __('Comment has been deleted successfully.'));
        }

        return redirect()->back()->with(SERVICE_RESPONSE_ERROR, __('This Comment can not be deleted.'));
    }
}
