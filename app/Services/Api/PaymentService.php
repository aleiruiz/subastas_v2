<?php

namespace App\Services\Api;

use App\Models\User\User;
use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\TransactionInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Repositories\User\Interfaces\WalletInterface;
use App\Repositories\User\Interfaces\WalletTransactionInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentService
{
    private $walletTransactionRepository;
    private $userRepository;
    private $transactionRepository;
    private $notificationRepository;
    private $walletRepository;

    public function __construct(WalletTransactionInterface $walletTransaction, UserInterface $user, TransactionInterface $transaction, NotificationInterface $notification, WalletInterface $wallet)
    {
        $this->walletTransactionRepository = $walletTransaction;
        $this->userRepository = $user;
        $this->transactionRepository = $transaction;
        $this->notificationRepository = $notification;
        $this->walletRepository = $wallet;
    }

    public function processPayment($paymentResponse)
    {
        if ($paymentResponse['error'] === 'ok') {
            if ($paymentResponse['result']['webhook_type'] == 'withdrawal') {
                $paymentResponse['result']['txn_status'] == 'completed' ?
                    $this->completeWithdrawal($paymentResponse['result']) :
                    $this->cancelWithdrawal($paymentResponse['result']);
            } else {
                $paymentResponse['result']['txn_status'] == 'completed' ?
                    $this->completeDeposit($paymentResponse['result']) :
                    $this->cancelDeposit($paymentResponse['result']);
            }
        }
    }

    public function completeWithdrawal($paymentResponse)
    {
        DB::transaction(function () use ($paymentResponse) {
            $walletTransactionConditions = [
                'payment_txn_id' => $paymentResponse['id'],
                'payment_method' => $paymentResponse['payment_method'],
                'status' => PAYMENT_STATUS_PENDING
            ];
            $walletTransactionAttributes = ['status' => PAYMENT_STATUS_COMPLETED, 'payment_txn_id' => $paymentResponse['txn_id'], 'network_fee' => $paymentResponse['fee']];
            $walletTransaction = $this->walletTransactionRepository->updateByConditions($walletTransactionAttributes, $walletTransactionConditions);

            $walletAttributes = ['balance' => DB::raw('balance + ' . $walletTransaction->system_fee)];
            $this->userRepository->update($walletAttributes, FIXED_USER_SUPER_ADMIN);

            $date = now();
            $route = route('user-currency.index');
            $notificationAttributes = [
                [
                    'user_id' => $walletTransaction->user_id,
                    'data' => __('Your withdrawal request of :currency :amount to :address has been confirmed by :paymentMethod with Txn ID - :txnId. The amount has been transferred already.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->amount, 'address' => $walletTransaction->email_address, 'paymentMethod' => payment_methods($walletTransaction->payment_method), 'txnId' => $walletTransaction->payment_txn_id]),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => FIXED_USER_SUPER_ADMIN,
                    'data' => __(':currency :amount withdrawal fee has been added as earning to your balance for this txn id - :txn_id.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->system_fee, 'txnId' => $paymentResponse['txn_id']]),
                    'link' => $route,
                    'updated_at' => $date,
                    'created_at' => $date,
                ]
            ];
            $this->notificationRepository->insert($notificationAttributes);

            $refId = Str::uuid();
            $transactionAttributes = [
                [
                    'user_id' => $walletTransaction->user_id,
                    'wallet_id' => $walletTransaction->wallet->id,
                    'model_id' => null,
                    'model' => null,
                    'ref_id' => $refId,
                    'amount' => bcmul($walletTransaction->amount, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => INCREASED_TO_OUTSIDE_AS_WITHDRAWAL_CONFIRMATION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => $walletTransaction->user_id,
                    'model' => get_class($walletTransaction->wallet),
                    'wallet_id' => $walletTransaction->wallet_id,
                    'model_id' => $walletTransaction->wallet_id,
                    'ref_id' => $refId,
                    'amount' => bcmul($walletTransaction->amount, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => DECREASED_FROM_USER_WALLET_AS_WITHDRAWAL,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                // system fee
                [
                    'user_id' => $walletTransaction->user_id,
                    'model' => get_class($walletTransaction->wallet),
                    'wallet_id' => $walletTransaction->wallet_id,
                    'model_id' => $walletTransaction->wallet_id,
                    'ref_id' => $refId,
                    'amount' => bcmul($walletTransaction->system_fee, "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => DECREASED_FROM_USER_WALLET_AS_WITHDRAWAL_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => FIXED_USER_SUPER_ADMIN,
                    'model' => get_class($walletTransaction->wallet),
                    'wallet_id' => $walletTransaction->wallet_id,
                    'model_id' => $walletTransaction->wallet_id,
                    'ref_id' => $refId,
                    'amount' => bcmul($walletTransaction->system_fee, "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => INCREASED_TO_SYSTEM_WALLET_AS_WITHDRAWAL_FEES,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
            ];
            $this->transactionRepository->insert($transactionAttributes);
        }, 3);
    }

    public function cancelWithdrawal($paymentResponse)
    {
        DB::transaction(function () use ($paymentResponse) {
            $walletTransactionConditions = [
                'payment_txn_id' => $paymentResponse['id'],
                'payment_method' => $paymentResponse['payment_method'],
                'status' => PAYMENT_STATUS_PENDING
            ];
            $walletTransactionAttributes = ['status' => PAYMENT_STATUS_FAILED, 'payment_txn_id' => $paymentResponse['txn_id']];
            $walletTransaction = $this->walletTransactionRepository->updateByConditions($walletTransactionAttributes, $walletTransactionConditions);

            $walletAttributes = ['balance' => DB::raw('balance + ' . $walletTransaction->amount)];
            $this->userRepository->update($walletAttributes, $walletTransaction->user_id);

            $date = now();
            $route = route('user-currency.index');
            $notificationAttributes = [
                'user_id' => $walletTransaction->user_id,
                'data' => __('Your withdrawal request of :currency :amount to :address has been declined by :paymentMethod with Txn ID - :txnId.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->amount, 'address' => $walletTransaction->email_address, 'paymentMethod' => payment_methods($walletTransaction->payment_method), 'txnId' => $walletTransaction->payment_txn_id]),
                'link' => $route,
                'updated_at' => $date,
                'created_at' => $date
            ];
            $this->notificationRepository->create($notificationAttributes);
        }, 3);
    }

    public function completeDeposit($paymentResponse)
    {
        try{
            DB::beginTransaction();
            $walletTransactionConditions = [
                'payment_txn_id' => $paymentResponse['txn_id'],
                'payment_method' => $paymentResponse['payment_method'],
                'status' => PAYMENT_STATUS_PENDING
            ];
            $walletTransactionAttributes = ['status' => PAYMENT_STATUS_COMPLETED, 'network_fee' => $paymentResponse['fee']];
            $walletTransaction = $this->walletTransactionRepository->updateByConditions($walletTransactionAttributes, $walletTransactionConditions);

            if( !$walletTransaction )
            {
                throw new \Exception('Failed to update wallet transaction on paypal deposit for paypal txn id: ' . $paymentResponse['txn_id']);
            }

            $walletAttributes = ['balance' => DB::raw('balance + ' . $paymentResponse['amount'])];
            if( !$this->walletRepository->update($walletAttributes, $walletTransaction->wallet_id) )
            {
                throw new \Exception('Failed to deposit wallet balance for wallet ID: ' . $walletTransaction->wallet_id . ' and  paypal txn id: ' . $paymentResponse['txn_id']);
            }

            $date = now();
            $route = route('user-currency.index');
            $notificationAttributes = [
                'user_id' => $walletTransaction->user_id,
                'data' => __('Your deposit request of :currency :amount has been confirmed by :paymentMethod with Txn ID - :txnId. The amount has been already added to your balance.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->amount, 'paymentMethod' => payment_methods($walletTransaction->payment_method), 'txnId' => $walletTransaction->payment_txn_id]),
                'link' => $route,
                'updated_at' => $date,
                'created_at' => $date
            ];
            $this->notificationRepository->create($notificationAttributes);

            $refId = Str::uuid();

            $date = now();
            $transactionAttributes = [
                [
                    'user_id' => $walletTransaction->user_id,
                    'wallet_id' => $walletTransaction->wallet_id,
                    'model_id' => $walletTransaction->wallet_id,
                    'model' => get_class($walletTransaction->wallet),
                    'ref_id' => $refId,
                    'amount' => bcmul($paymentResponse['amount'], "1"),
                    'journal_type' => JOURNAL_TYPE_CREDIT,
                    'journal' => INCREASED_TO_USER_WALLET_AS_DEPOSIT_CONFIRMATION,
                    'updated_at' => $date,
                    'created_at' => $date,
                ],
                [
                    'user_id' => $walletTransaction->user_id,
                    'wallet_id' => $walletTransaction->wallet_id,
                    'model_id' => null,
                    'model' => null,
                    'ref_id' => $refId,
                    'amount' => bcmul($paymentResponse['amount'], "-1"),
                    'journal_type' => JOURNAL_TYPE_DEBIT,
                    'journal' => DECREASED_FROM_OUTSIDE_AS_DEPOSIT,
                    'updated_at' => $date,
                    'created_at' => $date,
                ]
            ];
            $this->transactionRepository->insert($transactionAttributes);

            DB::commit();

        }
        catch (\Exception $exception)
        {
            DB::rollBack();

            logs()->error($exception->getMessage());
            logs()->error($exception->getFile());
            logs()->error($exception->getLine());
        }
    }

    public function cancelDeposit($paymentResponse)
    {
        DB::transaction(function () use ($paymentResponse) {
            $walletTransactionConditions = [
                'payment_txn_id' => $paymentResponse['txn_id'],
                'payment_method' => $paymentResponse['payment_method'],
                'status' => PAYMENT_STATUS_PENDING
            ];
            $walletTransactionAttributes = ['status' => PAYMENT_STATUS_FAILED];
            $walletTransaction = $this->walletTransactionRepository->updateByConditions($walletTransactionAttributes, $walletTransactionConditions);

            $date = now();
            $route = route('user-currency.index');
            $notificationAttributes = [
                'user_id' => $walletTransaction->user_id,
                'data' => __('Your deposit request of :currency :amount has been declined by :paymentMethod with Txn ID - :txnId.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->amount, 'paymentMethod' => payment_methods($walletTransaction->payment_method), 'txnId' => $walletTransaction->payment_txn_id]),
                'link' => $route,
                'updated_at' => $date,
                'created_at' => $date
            ];
            $this->notificationRepository->create($notificationAttributes);
        }, 3);
    }

    public function handleRealCurrencyPayment($paymentRequest)
    {
        if ($paymentRequest->payment_method == PAYMENT_METHOD_PAYPAL) {
            return $this->paypalPayment($paymentRequest);
        }

        // here goes conditionally new payment method

        return [
            SERVICE_RESPONSE_STATUS => false,
            SERVICE_RESPONSE_MESSAGE => __('Invalid payment method.')
        ];
    }

    public function paypalPayment($paymentRequest)
    {
        $paymentService = app(PaypalRestApi::class);
        $relatedTransaction = [
            'intent' => 'sale',
            'return_url' => route('paypal.return-url'),
            'cancel_url' => route('paypal.cancel-url'),
        ];

        $paymentResponse = $paymentService->payment($paymentRequest->amount, CURRENCY_TYPE_USD, $relatedTransaction);

        if (!$paymentResponse) {
            return [
                SERVICE_RESPONSE_STATUS => false,
                SERVICE_RESPONSE_MESSAGE => __('Paypal payment method is unavailable.')
            ];
        }

        session()->put(
            'PaypalPayment', [
                'wallet_transaction_id' => $paymentRequest->id,
                'payment_id' => $paymentResponse['payment_id']
            ]
        );

        return [
            SERVICE_RESPONSE_STATUS => true,
            SERVICE_RESPONSE_MESSAGE => __('The payment is created successfully.'),
            SERVICE_RESPONSE_DATA => [
                'isRedirectAway' => true,
                'redirectUrl' => $paymentResponse['return_url']
            ]
        ];
    }
}
