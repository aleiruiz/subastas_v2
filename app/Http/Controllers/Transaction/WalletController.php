<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Services\Core\DataListService;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public $wallet;

    public function __construct(WalletInterface $wallet)
    {
        $this->wallet = $wallet;
    }

    public function index()
    {
        $condition = [
            'user_id' => Auth::user()->id
        ];

        $searchFields = [
            ['balance', __('Balance')],
            ['on_order', __('On Order')],
        ];
        $orderFields = [
            ['balance', __('Balance')],
            ['currency_id', __('Currency')],
            ['on_order', __('On Order')],
            ['is_system', __('System')],
        ];

        $data['wallets'] = $this->wallet->paginateWithFilters($searchFields, $orderFields, $condition);
        $data['list'] = app(DataListService::class)->dataList($data['wallets'], $searchFields, $orderFields);
        $data['title'] = 'My Wallet';

        return view('frontend.user_access.transaction.wallet', $data);
    }
}
