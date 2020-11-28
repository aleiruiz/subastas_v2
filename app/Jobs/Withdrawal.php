<?php

namespace App\Jobs;

use App\Repositories\User\Interfaces\NotificationInterface;
use App\Repositories\User\Interfaces\UserInterface;
use App\Repositories\User\Interfaces\WalletTransactionInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class Withdrawal implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $walletTransaction;
    private $walletTransactionRepository;
    private $apiPath = 'App\\Services\\Api\\';

    public function __construct($walletTransaction)
    {
        $this->walletTransaction = $walletTransaction;
    }

    public function handle()
    {
        $actualAmount = bcsub($this->walletTransaction->amount, $this->walletTransaction->system_fee);
        $paymentApiClass = $this->apiPath . payment_method_api($this->walletTransaction->payment_method);
        $paymentService = new $paymentApiClass();
        $paymentServiceResponse = $paymentService->payout($this->walletTransaction->address, $actualAmount);

        $walletTransactionRepository = app(WalletTransactionInterface::class);

        if (strtolower($paymentServiceResponse['error']) === 'ok') {
            $walletTransactionRepository->update(['payment_txn_id' => $paymentServiceResponse['result']['txn_id']], $this->walletTransaction->id);
        }
        else {
            $walletTransaction = $walletTransactionRepository->update(['status' => PAYMENT_STATUS_FAILED], $this->walletTransaction->id);

            $userAttributes = ['balance' => DB::raw('balance + ' . $walletTransaction->amount)];
            app(UserInterface::class)->update($userAttributes, $walletTransaction->user_id);

            $notifications = [
                'user_id' => auth()->id(),
                'data' => __('Your withdrawal request of :currency :amount to :address has been failed with Ref ID - :refId. The amount has been reversed to your balance.', ['currency' => CURRENCY_TYPE_USD, 'amount' => $walletTransaction->amount, 'address' => $walletTransaction->email_address, 'refId' => $walletTransaction->txn_id])
            ];
            app(NotificationInterface::class)->create($notifications);
        }
    }
}
