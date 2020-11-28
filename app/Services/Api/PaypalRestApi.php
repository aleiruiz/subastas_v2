<?php

namespace App\Services\Api;

use PayPal\Api\Amount;
use PayPal\Api\Currency;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Payout;
use PayPal\Api\PayoutItem;
use PayPal\Api\PayoutSenderBatchHeader;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Exception\PayPalConnectionException;
use PayPal\Rest\ApiContext;

class PaypalRestApi
{
    private $apiContext;

    public function __construct()
    {
        /** PayPal api context **/
        $keys = ['paypal_client_id', 'paypal_secret', 'paypal_mode'];
        $paypalConfig = settings($keys, true);
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $paypalConfig['paypal_client_id'],
                $paypalConfig['paypal_secret']
            )
        );

        $settings = [
            'mode' => $paypalConfig['paypal_mode'],
            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path() . '/logs/paypal.log',
            'log.LogLevel' => 'ERROR'
        ];

        $this->apiContext->setConfig($settings);
    }

    public function payment($amount, $currency, $relatedTransaction = null)
    {
        if (!in_array($currency, $this->paypalAllowedCurrency())) {
            return false;
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amountConfig = new Amount();
        $amountConfig->setTotal($amount);
        $amountConfig->setCurrency($currency);

        $transaction = new Transaction();
        $transaction->setAmount($amountConfig);

        $redirectUrls = new RedirectUrls();
        $return_url = isset($relatedTransaction['return_url']) && !empty($relatedTransaction['return_url']) ? $relatedTransaction['return_url'] : $this->apiContext['return_url'];
        $cancel_url = isset($relatedTransaction['cancel_url']) && !empty($relatedTransaction['cancel_url']) ? $relatedTransaction['cancel_url'] : $this->apiContext['cancel_url'];
        $redirectUrls->setReturnUrl($return_url)
            ->setCancelUrl($cancel_url);

        $payment = new Payment();
        $paypalIntent = ["sale", "authorize", "order"];
        $intent = isset($relatedTransaction['intent']) && in_array($relatedTransaction['intent'], $paypalIntent) ? $relatedTransaction['intent'] : $this->apiContext['intent'];
        $payment->setIntent($intent)
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($this->apiContext);
        } catch (PayPalConnectionException $exception) {
            return false;
        }

        return [
            'return_url' => $payment->getApprovalLink(),
            'payment_id' => $payment->getId(),
        ];
    }

    private function paypalAllowedCurrency()
    {
        return ['AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'];
    }

    public function getPaymentStatus($paymentId, $payerId)
    {
        $payment = Payment::get($paymentId, $this->apiContext);
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        return $payment->execute($execution, $this->apiContext);
    }

    public function payout($receiver, $value, $currency = 'USD', $recipientType = 'Email')
    {
        $payouts = new Payout();
        $senderBatchHeader = new PayoutSenderBatchHeader();
        $senderBatchHeader->setSenderBatchId(uniqid())
            ->setEmailSubject("You have a Payout from " . company_name() . "!");

        $senderItem = new PayoutItem();
        $senderItem->setRecipientType($recipientType)
            ->setNote('You have received a payout! Thanks for being with ' . company_name() . ' !')
            ->setReceiver($receiver)
            ->setSenderItemId(uniqid())
            ->setAmount(new Currency('{
                        "value":"' . $value . '",
                        "currency":"' . $currency . '"
                    }'));
        $payouts->setSenderBatchHeader($senderBatchHeader)
            ->addItem($senderItem);

        try {
            $params = array('sync_mode' => 'false');
            $response = $payouts->create($params, $this->apiContext);

            return [
                'error' => 'ok',
                'result' => [
                    'txn_id' => $response->batch_header->payout_batch_id,
                ]
            ];

        } catch (PayPalConnectionException $exception) {
            return [
                'error' => $exception->getMessage()
            ];
        }
    }

    public function validateWebHook($request)
    {
        $paypalWebHookId = settings('paypal_webhook_id');
//        $paypalWebHookId = '1K979666U43826643';
        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($request->header('paypal-auth-algo'));
        $signatureVerification->setTransmissionId($request->header('paypal-transmission-id'));
        $signatureVerification->setCertUrl($request->header('paypal-cert-url'));
        $signatureVerification->setWebhookId($paypalWebHookId); // 1K979666U43826643 Note that the Webhook ID must be a currently valid Webhook that you created with your client ID/secret.
        $signatureVerification->setTransmissionSig($request->header('paypal-transmission-sig'));
        $signatureVerification->setTransmissionTime($request->header('paypal-transmission-time'));
        $signatureVerification->setRequestBody( json_encode($request->all()));

        try {
            $output = $signatureVerification->post($this->apiContext);
            return $output->getVerificationStatus();
        } catch (\Exception $ex) {
            return false;
        }
    }

    public function validateIPN($request)
    {
        try {
            if (strtoupper($this->validateWebHook($request)) === PAYPAL_WEBHOOK_VALIDATION_SUCCESS) {
                $webHookResponse = $request->all();

                $webHookType = null;
                $txnStatus = null;

                if ( strtoupper($webHookResponse['event_type']) == PAYPAL_PAYMENT_SALE_COMPLETED )
                {
                    $webHookType = 'deposit';
                    $txnStatus = 'completed';
                }
                elseif(strtoupper($webHookResponse['event_type']) == PAYPAL_PAYMENT_PAYOUTS_ITEM_SUCCEEDED)
                {
                    $webHookType = 'withdrawal';
                    $txnStatus = 'completed';
                }
                elseif( in_array( strtoupper($webHookResponse['event_type'] ), paypal_payment_webhook_type() ) ) {
                    $webHookType = 'deposit';
                    $txnStatus = 'failed';
                }
                elseif( in_array( strtoupper($webHookResponse['event_type'] ), paypal_payout_webhook_type() ) ) {
                    $webHookType = 'withdrawal';
                    $txnStatus = 'failed';
                }

                if( !empty($txnStatus) && !empty($webHookType) ) {
                    $webHookResponseResource = $webHookResponse['resource'];
                    if ($webHookType == 'withdrawal') {
                        $address = $webHookResponseResource['payout_item']['receiver'];
                        $txnId = $webHookResponseResource['transaction_id'];
                        $id = $webHookResponseResource['payout_batch_id'];
                        $currency = $webHookResponseResource['payout_item']['amount']['currency'];
                        $amount = $webHookResponseResource['payout_item']['amount']['value'];
                        $fee = $webHookResponseResource['payout_item_fee']['value'];
                    } else {
                        $address = '';
                        $txnId = $id = $webHookResponseResource['id'];
                        $currency = $webHookResponseResource['amount']['currency'];
                        $fee = $webHookResponseResource['transaction_fee']['value'];
                        $amount = bcsub($webHookResponseResource['amount']['total'], $fee);
                    }

                    return [
                        'error' => 'ok',
                        'result' => [
                            'txn_status' => $txnStatus,
                            'payment_method' => PAYMENT_METHOD_PAYPAL,
                            'webhook_type' => $webHookType,
                            'address' => $address,
                            'txn_id' => $txnId,
                            'id' => $id,
                            'currency' => $currency,
                            'amount' => $amount,
                            'fee' => $fee,
                        ]
                    ];
                }
            }

            return ['error' => 'Invalid paypal webhook.'];
        } catch (\Exception $exception) {
            return ['error' => $exception->getMessage()];
        }
    }
}
