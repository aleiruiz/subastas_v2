<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Services\Core\DataListService;
use App\Services\Transaction\TransactionService;
use Illuminate\Support\Facades\Auth;

class TransactionHistoryController extends Controller
{


    public function index($userTransactionType = null)
    {
        $condition = [];
        if (!empty($userTransactionType)) {
            $condition['journal'] = $userTransactionType;
        } else {
            $condition[] = ['journal', 'in', array_keys(user_transaction_type())];
        }

        $searchFields = [
            ['amount', __('Amount')]
        ];
        $orderFields = [
            ['amount', __('Amount')],
        ];

        $earnings = app(TransactionInterface::class)->paginateWithFilters($searchFields, $orderFields, $condition);
        $data['list'] = app(DataListService::class)->dataList($earnings, $searchFields, $orderFields);
        $data['user'] = Auth::user();
        $data['transactionType'] = $userTransactionType;
        $data['title'] = 'Transactions';
        if (empty($userTransactionType)) {
            $userTransactionType = ['journal', 'in', array_keys(user_transaction_type())];
        }
        $data = array_merge($data, app(TransactionService::class)->transactionSummary($userTransactionType));

        return view('frontend.user_access.transaction.transaction_history', $data);
    }
}
