<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuctionRulesController extends Controller
{
    public function index()
    {
        $data['title'] = __('Auction');
        return view('frontend.global_access.auction_rules', $data);
    }
}
