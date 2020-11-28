<?php

use Illuminate\Support\Facades\Route;

Route::get('/', 'HomeController@index')->name('home');
Route::get('/auctions/{category?}', 'HomeController@allAuctionIndex')->name('auction.home');
Route::get('/auctions/type/{type?}', 'HomeController@allAuctionByTypeIndex')->name('auction-type.home');

Route::get('/contact-us', 'ContactUsController@create')->name('contact-us.create');
Route::post('/contact-us', 'ContactUsController@store')->name('contact-us.store');
Route::get('/auction-rules', 'AuctionRulesController@index')->name('auction-rules.index');
Route::get('/auctions/search', 'AuctionSearchController@index')->name('auction-search.index');

Route::get('auction/{id}', 'User\AuctionController@show')->name('auction.show');

//Test
Route::get('test', 'TestController@test')->name('test');
Route::post('tests', 'TestController@testPost')->name('testpost');

