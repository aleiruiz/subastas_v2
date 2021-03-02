<?php

Route::any('paypal/webhook', 'Api\WebHookController@paypalWebhook')->name('paypal.webhook');

Route::post('add_count_visits', 'User\AuctionController@add_count_visits')->name('add-count-visits');
Route::get('auction/bid_list/{id}', 'User\AuctionController@bid_list')->name('load.bidlist');