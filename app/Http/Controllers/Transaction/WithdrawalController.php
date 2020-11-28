<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\WithdrawalRequest;
use App\Jobs\Withdrawal;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Repositories\User\Interfaces\WalletTransactionInterface;
use App\Services\Core\DataListService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    private $repository;

    public function __construct(WalletTransactionInterface $walletTransaction)
    {
        $this->repository = $walletTransaction;
    }

    public function index($statusType = null)
    {
        $condition = [];
        if (Auth::user()->id != FIXED_USER_SUPER_ADMIN) {
            $condition = [
                'user_id' => Auth::user()->id,
                'txn_type' => TRANSACTION_TYPE_WITHDRAWAL,
            ];
        }
        if (!empty($statusType)) {
            $condition['status'] = $statusType;
        }

        $searchFields = [
            ['payment_txn_id', __('Transaction ID')],
            ['address', __('Email')],
            ['amount', __('Amount')],
        ];
        $orderFields = [
            ['txn_type', __('Transaction Type')],
            ['payment_method', __('Payment Method')],
            ['amount', __('Amount')],
            ['address', __('Email')],
            ['network_fee', __('Network Fee')],
            ['system_fee', __('System Fee')],
        ];

        $filters = [
            ['wallet_transactions.txn_type', __('Transaction Type'), payment_methods()],
            ['wallet_transactions.status', __('Status'), payment_status()],
        ];

        $query = $this->repository->paginateWithFilters($searchFields, $orderFields, $condition, $filters );
        $data['list'] = app(DataListService::class)->dataList($query, $searchFields, $orderFields, $filters);
        $data['user'] = Auth::user();
        $data['statusType'] = $statusType;
        $data['title'] = 'Withdrawal History';
        return view('frontend.user_access.transaction.withdrawal_history', $data);
    }

    public function create($id)
    {
        $data['title'] = __('Withdrawal Processes');
        $data['wallet'] = app(WalletInterface::class)->findOrFailByConditions(['id' => $id, 'user_id' => auth()->id()], 'currency');
        $data['user'] = Auth::user();
        return view('frontend.user_access.transaction.withdrawal', $data);
    }

    public function store(WithdrawalRequest $request, $id)
    {

        $wallet = app(WalletInterface::class)->findOrFailByConditions(['id' => $id, 'user_id' => auth()->id()], 'currency');
        if ($wallet->currency->is_active !== ACTIVE_STATUS_ACTIVE) {
            return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Withdrawal system is currently Inactive'));
        }

        DB::beginTransaction();

        $withdrawalFee = settings('withdrawal_fee');
        $withdrawalFeeAmount = bcdiv(bcmul($request->amount, $withdrawalFee), "100");

        $walletAttributes = ['balance' => DB::raw('balance - ' . $request->amount)];

        if (!app(WalletInterface::class)->update($walletAttributes, $wallet->id)) {
            DB::rollBack();
            return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Failed to withdraw the amount. Please try again.'));
        }

        $walletTransactionAttributes = $request->only('payment_method', 'amount', 'address');
        $walletTransactionAttributes['user_id'] = auth()->id();
        $walletTransactionAttributes['wallet_id'] = $id;
        $walletTransactionAttributes['txn_type'] = TRANSACTION_TYPE_WITHDRAWAL;
        $walletTransactionAttributes['system_fee'] = $withdrawalFeeAmount;
        $walletTransactionAttributes['ref_id'] = Str::uuid();
        $walletTransactionAttributes['status'] = PAYMENT_STATUS_PENDING;

        $walletTransaction = $this->repository->create($walletTransactionAttributes);

        $notifications = [
            'user_id' => auth()->id(),
            'data' => __('Your withdrawal request of :currency :amount to :address has been approved by :paymentMethod with Ref ID - :refId . The amount will be transferred after :paymentMethod confirmation.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $request->amount, 'address' => $request->address, 'paymentMethod' => payment_methods($request->payment_method), 'refId' => $walletTransaction->txn_id])
        ];
        app(NotificationInterface::class)->create($notifications);

        dispatch(new Withdrawal($walletTransaction));

        DB::commit();

        return redirect()->route('withdrawal.index')->with(SERVICE_RESPONSE_SUCCESS, __('Your withdrawal request of :currency :amount to :address has been approved by :paymentMethod with Ref ID - :refId . The amount will be transferred after :paymentMethod confirmation.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $request->amount, 'address' => $request->address, 'paymentMethod' => payment_methods($request->payment_method), 'refId' => $walletTransaction->txn_id]));
    }
}
