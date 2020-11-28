<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\WalletTransactionRequest;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Repositories\User\Interfaces\WalletTransactionInterface;
use App\Services\Api\PaymentService;
use App\Services\Api\PaypalRestApi;
use App\Services\Core\DataListService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WalletTransactionController extends Controller
{
    public $repository;

    public function __construct(WalletTransactionInterface $repository)
    {
        $this->repository = $repository;
    }

    public function index($statusType = null)
    {
        $condition = [];
        if (Auth::user()->id != FIXED_USER_SUPER_ADMIN) {
            $condition = [
                'user_id' => Auth::user()->id,
                'txn_type' => TRANSACTION_TYPE_DEPOSIT,
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
        $data['title'] = 'Deposit History';
        return view('frontend.user_access.transaction.deposit_history', $data);
    }

    public function create($id)
    {
        $data['wallet'] = app(WalletInterface::class)->findOrFailByConditions(['id' => $id, 'user_id' => auth()->id()], 'currency');
        $data['title'] = __('Deposit');
        $data['user'] = Auth::user();

        return view('frontend.user_access.transaction.deposit', $data);
    }

    public function store(WalletTransactionRequest $request, $id)
    {

        $errorMessage = __('Failed to deposit. Please try again.');

        $wallet = app(WalletInterface::class)->findOrFailByConditions(['id' => $id, 'user_id' => auth()->id()], 'currency');
        if ($wallet->currency->is_active !== ACTIVE_STATUS_ACTIVE) {
            return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, __('Deposit system is currently Inactive'));
        }

        try {
            DB::beginTransaction();

            $parameters = $request->only('payment_method', 'amount');
            $parameters['user_id'] = auth()->id();
            $parameters['wallet_id'] = $id;
            $parameters['status'] = PAYMENT_STATUS_PENDING;
            $parameters['txn_type'] = TRANSACTION_TYPE_DEPOSIT;
            $parameters['ref_id'] = Str::uuid();

            $walletTransaction = $this->repository->create($parameters);

            $response = app(PaymentService::class)->handleRealCurrencyPayment($walletTransaction);

            if ($response[SERVICE_RESPONSE_STATUS] == false) {
                throw new Exception($response[SERVICE_RESPONSE_MESSAGE]);
            }

            DB::commit();

            if ($response[SERVICE_RESPONSE_DATA]['isRedirectAway']) {
                return redirect()->away($response[SERVICE_RESPONSE_DATA]['redirectUrl']);
            }

            return redirect()->route('deposit.index')->withInput()->with(SERVICE_RESPONSE_SUCCESS, $response[SERVICE_RESPONSE_MESSAGE]);

        } catch (Exception $exception) {
            DB::rollback();

            $errorMessage = $exception->getMessage();
        }

        return redirect()->back()->withInput()->with(SERVICE_RESPONSE_ERROR, $errorMessage);
    }

    public function paypalReturnUrl(Request $request)
    {
        $payment = session()->get('PaypalPayment');

        if (empty($payment) || empty($request->get('PayerID')) || empty($request->get('token'))) {
            abort(404);
        }

        session()->forget('PaypalPayment');

        $paymentStatus = app(PaypalRestApi::class)->getPaymentStatus($payment['payment_id'], $request->get('PayerID'));

        if ($paymentStatus && $paymentStatus->getState() == 'approved') {
            $walletTransactionAttributes = [
                'payment_txn_id' => $paymentStatus->transactions[0]->related_resources[0]->sale->id,
                'address' => $paymentStatus->payer->payer_info->email,
            ];
            $walletTransaction = $this->repository->update($walletTransactionAttributes, $payment['wallet_transaction_id']);

            $date = now();
            $route = route('user-currency.index');
            $notificationAttributes = [
                'user_id' => auth()->id(),
                'data' => __('Your deposit request of :currency :amount has been approved by :paymentMethod with Txn ID - :txnId. The amount will be transferred after :paymentMethod confirmation.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->amount, 'paymentMethod' => payment_methods($walletTransaction->payment_method), 'txnId' => $walletTransaction->payment_txn_id]),
                'link' => $route,
                'updated_at' => $date,
                'created_at' => $date,
                ];
            app(NotificationInterface::class)->create();

            return redirect()->route('deposit.index')->with(SERVICE_RESPONSE_SUCCESS, __('The transaction has been approved by PayPal!'));
        }

        $this->repository->update(['status' => PAYMENT_STATUS_FAILED], $payment['wallet_transaction_id']);

        return redirect()->route('deposit.index')->with(SERVICE_RESPONSE_ERROR, __('The transaction has been declined by PayPal!'));
    }

    public function paypalCancelUrl()
    {
        $payment = session()->get('PaypalPayment');

        if (!$payment) {
            abort(404);
        }

        session()->forget('PaypalPayment');

        $this->repository->update(['status' => PAYMENT_STATUS_CANCELED], $payment['wallet_transaction_id']);

        return redirect()->route('wallet-deposit.create')->with(SERVICE_RESPONSE_WARNING, __('The transaction has been canceled!'));
    }
}
