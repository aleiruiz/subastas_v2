<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\PaymentService;
use App\Services\Api\PaypalRestApi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebHookController extends Controller
{
    public function paypalWebhook(Request $request)
    {
        $paypalApi = new PaypalRestApi();
        $paypaWebHookResponse = $paypalApi->validateIPN($request);
        app(PaymentService::class)->processPayment($paypaWebHookResponse);
    }
}
