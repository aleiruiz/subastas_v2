<?php

Route::any('paypal/webhook', 'Api\WebHookController@paypalWebhook')->name('paypal.webhook');

Route::post('add_count_visits', 'User\AuctionController@add_count_visits')->name('add-count-visits');
