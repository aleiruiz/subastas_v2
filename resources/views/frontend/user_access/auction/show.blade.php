@extends('frontend.layouts.master')
@section('content')

    <!-- Login Modal -->
<div class="custom-modal">
    <div class="modal fade" id="confirmBidModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header text-center">
                    <img src="{{asset('public/icons/garantia.png')}}" width="100px">
                    <h5 class="modal-title justify-content-center font-weight-bold" id="exampleModalLongTitle">Garantia: {{ !is_null($auction->currency) ? $auction->currency->symbol : ''}} {{$auction->bid_initial_price * 0.10}}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-center">
                    <div class="row">
                        <div class="col-lg-12 col-md-12">
                             Al consignar la garantia <br>
                            aceptas a conectarte a la sala <br>
                            "En vivo" y enviar por lo menos una puja valida <br>
                            durante el proceso en vivo.
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                        <a class="btn btn-success"  id="confirmBid">Aceptar</a>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- ::::::::::::::::::::::START PAGE HEAD ::::::::::::::::::::::::: -->
    <div class="p-b-100 p-t-80">
        <div class="container">
            <div class="row">

                <div class="col-12 mb-4">
                    @include('layouts.includes.breadcrumb')
                </div>

                <!-- Start: property title details -->
                <div class="col-12">
                    <div class="property-title m-b-50">

                        <!-- Start: property top -->
                        <div class="property-top">

                            <div class="row">
                                <div class="col-lg-8 col-md-12">

                                    <!-- Start: property title -->
                                    <div class="item-name">{{$auction->title}}</div>
                                    <!-- End: property title -->

                                    <!-- Start: property area -->
                                    <div class="property-area">
                                        <i class="fa fa-map-marker"></i>
                                        {{!is_null($auction->seller->user->profile) ? $auction->seller->user->profile->address : ''}}
                                    </div>
                                    <!-- End: property area -->

                                    <div class="property-area text-uppercase">
                                        <i class="fa fa-th-large" aria-hidden="true"></i>
                                        REFERENCIA:
                                        {{$auction->ref_id}}
                                    </div>

                                    <!-- Start: property overview -->
                                    <div class="property-overview mt-1">
                                        <ul class="nav">
                                            <li class="color-999">
                                                <i class="fa fa-flag"></i>
                                                Subastador <a
                                                    href="{{route('seller-profile.show', $auction->seller_id)}}">{{ !is_null($auction->seller) ? $auction->seller->name : ''}}</a>
                                            </li>
                                            <li class="color-999">
                                                <i class="fa fa-list-alt"></i>
                                                {{ !is_null($auction->category) ? $auction->category->name : ''}}
                                            </li>
                                            <li class="color-999">
                                                <i class="fa fa-money"></i>
                                                {{ !is_null($auction->currency) ? $auction->currency->symbol : ''}}
                                            </li>
                                            <li class="color-999">
                                                <i class="fa fa-clock-o"></i>
                                                {{ !is_null($auction->ending_date) ? $carbon->parse($auction->ending_date)->diffForHumans() : ''}}
                                            </li>
                                        </ul>
                                    </div>
                                    <!-- End: property overview -->

                                </div>

                                <div class="col-lg-4 col-md-12">

                                    <!-- Start: property price -->
                                    <div class="property-price align-self-center">
                                        <h4 class="m-b-10 font-weight-bold text-capitalize">{{__('Bid Start From')}}</h4>
                                        <div class="mb-2 color-999">
                                            <span>{{empty($auction->currency->symbol) ? '' : $auction->currency->symbol}}</span>
                                            {{$auction->bid_initial_price}}
                                        </div>
                                        <span
                                            class="badge {{config('commonconfig.auction_status.' . ( !is_null($auction) ? $auction->status : AUCTION_STATUS_COMPLETED ) . '.color_class')}}">{{ config('commonconfig.auction_status.' . ( !is_null($auction) ? $auction->status : AUCTION_STATUS_COMPLETED ) . '.text')}}</span>
                                    </div>
                                    <!-- End: property price -->

                                </div>
                            </div>

                        </div>
                        <!-- End: property top -->

                    </div>
                </div>
                <!-- End: property title details -->

                <!-- Start: blog grid -->
                <div class="col-md-12 col-lg-7 order-lg-0">
                    <div class="m-md-top-50 bg-custom-gray border">

                        <!-- Start: properties slider -->
                        <div class="owl-six position-relative">

                            <!-- Start: main image -->
                            <div id="sync1" class="owl-carousel owl-theme">
                                @include('layouts.includes.slider_image')
                            </div>
                            <!-- End: main image -->

                            <!-- Start: image nav -->
                            <div id="sync2" class="owl-carousel owl-six-2 owl-theme">
                                @include('layouts.includes.slider_image')
                            </div>
                            <!-- End: image nav -->

                            <div class="dispute-link position-absolute">
                                <a class="flex-sm-fill text-sm-center nav-link p-0" data-toggle="dropdown"
                                   aria-haspopup="true" aria-expanded="false" href="#">
                                    <i class="fa fa-th-list icon-round"></i>
                                </a>
                                <div class="address-dropdown-menu">
                                    <div class="dropdown-menu  drop-menu dropdown-menu-right">
                                        <a class="p-2 d-block"
                                           href="{{route('disputes.specific', [DISPUTE_TYPE_AUCTION_ISSUE, $auction->ref_id])}}">
                                            {{__('Report Auction')}}
                                        </a>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- End: properties slider -->

                    </div>
                </div>
                <!-- End: blog grid -->

                <!-- Start: bidding section -->
                <div class="col-md-12 col-lg-5 order-lg-0">

                    <div class="s-box">
                        @auth
                            @if(!is_null($userLastBid))
                                <ul class="list-group mt-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Your Last Bid :')}}
                                        </span>
                                        <span class="badge border color-666 badge-pill"> <span
                                                class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span> {{$userLastBid->amount}}</span>
                                    </li>
                                </ul>
                            @endif
                        @endauth
                    </div>

                    <div class="s-box mb-3">
                        <!-- Start: header -->

                        <div class="s-box-header">
                            <?php
                            $now = \Carbon\Carbon::now();
                            $start = \Carbon\Carbon::parse($auction->starting_date);
                            $end = \Carbon\Carbon::parse($auction->ending_date);
                             ?>
                            @if(\Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($auction->starting_date)))
                                <span> Termina </span>
                            @else
                                <span> Empieza </span>
                            @endif
                            {{__('In')}}
                        </div>
                        <!-- End: header -->
                        <!-- Start: countdown -->
                        <div class="count-down">
                            <div class="timer d-inline-block">
                                <Timer
                                    starttime="{{$start}}"
                                    endtime="{{$end}}"
                                    type='auction'
                                    trans='{
                                                "day":"D",
                                                "hours":"H",
                                                "minutes":"M",
                                                "seconds":"S",
                                                "expired":"Este evento a expirado.",
                                                "running":"Este evento esta en progreso.",
                                                "upcoming":"Este evento aun no a empezado.",
                                                "status": {
                                                        "expired":"Expirado",
                                                        "running":"En Progreso",
                                                        "upcoming":"Ya Viene"
                                                    }
                                                }'
                                ></Timer>
                            </div>
                        </div>
                        <!-- End: countdown -->
                    </div>


                    <div class="s-box">
                    @auth()
                        @include('layouts.includes.bidding_list')
                    @endauth
                    </div>

                    <!-- Start: bidding section -->
                    <div class="s-box">

                        <!-- Start: header -->
                        <div class="s-box-header">
                            <span> {{__('Bidding')}} </span>
                            {{__('Section')}}
                        </div>
                        <!-- End: header -->

                        <!-- Start: item list -->
                        <div class="popular-cat">
                            <!-- <ul class="list-group">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        {{__('Auction Type :')}}
                                    </span>
                                    <span
                                        class="badge badge-pill {{config('commonconfig.auction_type.' . ( !is_null($auction) ? $auction->auction_type : ACTIVE_STATUS_ACTIVE ) . '.color_class')}}">{{ config('commonconfig.auction_type.' . ( !is_null($auction) ? $auction->auction_type : ACTIVE_STATUS_ACTIVE ) . '.text')}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        {{__('Multiple Bid Allowed :')}}
                                    </span>
                                    <span
                                        class="badge badge-pill {{config('commonconfig.is_multi_bid_allowed.' . ( !is_null($auction) ? $auction->is_multiple_bid_allowed : ACTIVE_STATUS_ACTIVE ) . '.color_class')}}">{{ config('commonconfig.is_multi_bid_allowed.' . ( !is_null($auction) ? $auction->is_multiple_bid_allowed : ACTIVE_STATUS_ACTIVE ) . '.text')}}</span>
                                </li>
                            </ul> -->
                            <ul class="list-group mt-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        {{__('Total Bids :')}}
                                    </span>
                                    <span class="badge badge-primary badge-pill" id="count-bid">{{$auction->bids->count()}}</span>
                                </li>
                                @if($auction->auction_type != AUCTION_TYPE_UNIQUE_BIDDER && $auction->auction_type != AUCTION_TYPE_VICKREY_AUCTION)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Bid Increment Difference :')}}
                                        </span>
                                        <span
                                            class="badge badge-primary badge-pill"><span class="font-weight-normal"> {{$auction->currency->symbol}}</span> {{$auction->bid_increment_dif}}</span>
                                    </li>
                                @endif
                                @if($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER && settings('bidding_fee_on_highest_bidder_auction') > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Bidding Fee :')}}
                                        </span>
                                        <span
                                            class="badge badge-primary badge-pill"><span class="font-weight-normal"> {{$auction->currency->symbol}}</span> {{settings('bidding_fee_on_highest_bidder_auction')}}</span>
                                    </li>
                                @elseif($auction->auction_type == AUCTION_TYPE_BLIND_BIDDER && settings('bidding_fee_on_blind_bidder_auction') > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Bidding Fee :')}}
                                        </span>
                                        <span
                                            class="badge badge-primary badge-pill"><span class="font-weight-normal"> {{$auction->currency->symbol}}</span><span class="font-weight-normal"> {{$auction->currency->symbol}}</span> {{settings('bidding_fee_on_blind_bidder_auction')}}</span>
                                    </li>
                                @elseif($auction->auction_type == AUCTION_TYPE_UNIQUE_BIDDER && settings('bidding_fee_on_unique_bidder_auction') > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Bidding Fee :')}}
                                        </span>
                                        <span
                                            class="badge badge-primary badge-pill"><span class="font-weight-normal"> {{$auction->currency->symbol}}</span> {{settings('bidding_fee_on_unique_bidder_auction')}}</span>
                                    </li>
                                @elseif($auction->auction_type == AUCTION_TYPE_VICKREY_AUCTION && settings('bidding_fee_on_vickrey_bidder_auction') > 0)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Bidding Fee :')}}
                                        </span>
                                        <span
                                            class="badge badge-primary badge-pill"><span class="font-weight-normal"> {{$auction->currency->symbol}}</span><span class="font-weight-normal"> {{$auction->currency->symbol}}</span> {{settings('bidding_fee_on_vickrey_bidder_auction')}}</span>
                                    </li>
                                @endif
                                @if(count($auction->bids) > 0 && $auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            {{__('Highest Bid Amount:')}}
                                        </span>
                                        <span
                                            class="badge badge-primary badge-pill" id="max-bid"><span class="font-weight-normal"> {{$auction->currency->symbol}}</span> {{$auction->bids->max('amount')}}</span>
                                    </li>
                                @endif
                            </ul>
                            

                            @if(count($auction->bids) > 0 && $auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER)
                                <ul class="list-group mt-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold color-666">
                                            {{__('Next Minimum Bid Amount :')}}
                                        </span>
                                        <span class="badge bg-warning text-white badge-pill" id="minimum-bid"> <span class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span>{{$highestBid->amount + $auction->bid_increment_dif}}</span>
                                    </li>
                                </ul>
                                @elseif($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER)
                                <ul class="list-group mt-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold color-666">
                                            {{__('Minimum Bid Amount :')}}
                                        </span>
                                        <span class="badge bg-warning text-white badge-pill"> <span class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span>{{$auction->bid_initial_price}}</span>
                                    </li>
                                </ul>
                            @endif

                            @if($auction->auction_type == AUCTION_TYPE_BLIND_BIDDER)
                                <ul class="list-group mt-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold color-666">
                                            {{__('Minimum Bid Amount :')}}
                                        </span>
                                        <span class="badge bg-warning text-white badge-pill"> <span class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span>{{$auction->bid_initial_price}}</span>
                                    </li>
                                </ul>
                            @endif

                            @if($auction->auction_type == AUCTION_TYPE_UNIQUE_BIDDER)
                                <ul class="list-group mt-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold color-666">
                                            {{__('Minimum Bid Amount :')}}
                                        </span>
                                        <span class="badge bg-warning text-white badge-pill"> <span class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span>{{$auction->bid_initial_price}}</span>
                                    </li>
                                </ul>
                            @endif

                            @if($auction->auction_type == AUCTION_TYPE_VICKREY_AUCTION)
                                <ul class="list-group mt-3">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span class="font-weight-bold color-666">
                                            {{__('Minimum Bid Amount :')}}
                                        </span>
                                        <span class="badge bg-warning text-white badge-pill"> <span class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span>{{$auction->bid_initial_price}}</span>
                                    </li>
                                </ul>
                            @endif

                        </div>
                        <!-- End: item list -->

                        @auth
                        @if($auction->status == AUCTION_STATUS_RUNNING)
                            <div class="list-group mt-3">
                                <div class="list-group-item py-4">
                                    {{ Form::open(['route'=>['bid.store', $auctionId],'class'=>'form-horizontal cvalidate','id'=>'bidForm']) }}
                                    @method('post')
                                    @basekey

                                    <!-- Start: auction main content -->
                                    <div class="form-group">
                                        <span class="d-flex justify-content-center">
                                            <span class="input-number-decrement">–</span>
                                        {{ Form::text(fake_field('amount'), old('amount'), ['class' => 'input-number color-666', 'id' => fake_field('amount'), 'min'=>'0' ]) }}
                                        <span class="input-number-increment">+</span>
                                        </span>
                                        <span class="invalid-feedback cval-error d-block" data-cval-error="{{ fake_field('amount') }}">{{ $errors->first('amount') }}</span>
                                    </div>
                                    <!-- End: auction main content -->

                                    <a class="btn custom-btn w-100 float-right has-spinner" id="offer">{{__('Bid Your Amount')}}</a>

                                    {{ Form::close() }}
                                </div>
                            </div>
                        @endif
                        @endauth
                    </div>
                    <!-- End: bidding section -->

                </div>
                <!-- End: bidding section -->

                @auth
                <!-- Start: Winner info -->
                @if(!is_null($auction->address_id) && auth()->user()->seller ? $auction->seller_id == auth()->user()->seller->id : false && $auction->status != AUCTION_STATUS_RUNNING)
                    <div class="col-lg-12">
                        <div class="card mt-5">
                            <div class="card-body">
                                <h5 class="color-666 mb-3">{{__('Winner Address :')}}</h5>

                                <!-- Start: address card -->
                                <div class="card">
                                    <div class="card-body address-card winner-parent">

                                        <div class="agent-info">
                                            <div class="personal-info mx-2 my-4">
                                                <ul>
                                                    <li>
                                                        <span>
                                                            <i class="fa fa-user justify-content-center"></i>
                                                            name :
                                                        </span>
                                                        {{$address->name}}
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <i class="fa fa-map-marker"></i>
                                                            location :
                                                        </span>
                                                        {{$address->city}}
                                                        {{$address->country->name}}
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <i class="fa fa-phone"></i>
                                                            phone :
                                                        </span>
                                                        {{$address->phone_number}}
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <i class="fa fa-envelope"></i>
                                                            post code :
                                                        </span>
                                                        {{$address->post_code}}
                                                    </li>
                                                    <li>
                                                        <span>
                                                            <i class="fa fa-check-circle"></i>
                                                            Verification Status :
                                                        </span>

                                                        <span
                                                            class="badge d-inline-block w-auto badge-pill pr-2 text-white font-weight-normal {{config('commonconfig.verification_status.' . ( $address->is_verified !== VERIFICATION_STATUS_APPROVED ? VERIFICATION_STATUS_UNVERIFIED  : $address->is_verified) . '.color_class')}}"> {{verification_status($address->is_verified) }} </span>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                        @if($address->is_default == ACTIVE_STATUS_ACTIVE)
                                            <div class="default-badge">
                                                {{$address->is_default == ACTIVE_STATUS_ACTIVE ? 'Default Address' : ''}}
                                            </div>
                                        @endif
                                        <div class="winner-image position-absolute">
                                            <img class="img-fluid" src="{{asset('images/winner-badge.png')}}" alt="">
                                        </div>
                                    </div>
                                </div>
                                <!-- End: address card -->

                                <!-- Start: shipping instruction -->
                                <div class="m-4">
                                    <h5 class="color-666 border-bottom pb-3">{{__('Shipping instruction :')}}</h5>
                                    <p class="mt-3">{{$auction->shipping_description}}</p>
                                </div>
                                <!-- End: shipping instruction -->

                                <div class="m-4">
                                    <h5 class="color-666 mb-3">{{__('Shipping Status :')}}</h5>
                                    <span
                                        class="badge badge-pill {{config('commonconfig.product_claim_status.' . ( !is_null($auction) ? $auction->product_claim_status : '' ) . '.color_class')}}">{{ config('commonconfig.product_claim_status.' . ( !is_null($auction) ? $auction->product_claim_status : '' ) . '.text')}}</span>

                                    @if($auction->product_claim_status == AUCTION_PRODUCT_CLAIM_STATUS_NOT_DELIVERED_YET)
                                        <h5 class="color-666 mt-4">{{__('Please Submit Delivery date :')}}</h5>
                                        <span
                                            class="color-999 d-block fz-12">{{__('Expected date of Product receiving')}}</span>
                                        {{ Form::open(['route'=>['update-shipping-status.update',$auction->id],'class'=>'form-horizontal cvalidate', 'files' => true]) }}
                                        @method('put')
                                        @basekey

                                        <!-- Start: delivery date -->
                                        <div class="form-row mt-3">
                                            <div class="col-md-4">
                                                {{ Form::text(fake_field('delivery_date'), old('delivery_date'), ['class'=> 'form-control datepicker', 'id' => fake_field('delivery_date'),'data-cval-name' => 'The delivery_date field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Starting Date')]) }}
                                                <span class="invalid-feedback cval-error"
                                                      data-cval-error="{{ fake_field('delivery_date') }}">{{ $errors->first('delivery_date') }}</span>
                                            </div>
                                        </div>
                                        <!-- End: delivery date -->

                                        <button type="submit" class="btn btn-info mt-3">{{__('Submit Date')}}</button>

                                        {{ Form::close() }}
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>
                @endif
                <!-- End: Winner info -->
                @endauth

                <!-- Start: description area -->
                <div class="col-12">
                    <!-- Start: property details body -->
                    <div class="single-blog m-t-50">

                        <!-- Start: property tab -->
                        <div class="custom-profile-nav">

                            <!-- Start: tab -->
                            <nav>

                                <!-- Start: tab nav -->
                                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                                    <a class="nav-item nav-link active" id="description" data-toggle="tab"
                                       href="#descrip" role="tab" aria-controls="descrip"
                                       aria-selected="true">{{__('Product Description')}}</a>
                                    @if(!is_null($auction->terms_description))
                                        <a class="nav-item nav-link" id="features" data-toggle="tab" href="#featu"
                                           role="tab" aria-controls="featu"
                                           aria-selected="false">{{__('Term Description')}}</a>
                                    @endif
                                    @auth
                                    @if($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER)
                                        <a class="nav-item nav-link" id="amenties" data-toggle="tab" href="#amenti"
                                           role="tab" aria-controls="amenti"
                                           aria-selected="false">{{__('Bidding History')}}</a>
                                    @endif
                                    @endauth
                                </div>
                                <!-- Start: tab nav -->

                            </nav>
                            <!-- End: tab -->

                            <!-- Start: property tab body -->
                            <div class="tab-content" id="nav-tabContent">

                                <!-- Start: description body -->
                                <div class="tab-pane fade show active" id="descrip" role="tabpanel"
                                     aria-labelledby="description">

                                    <!-- Start: description -->
                                    <div class="m-t-50">

                                        <p class="single-blog-details text-justify">
                                            {{view_html($auction->product_description)}}
                                        </p>

                                    </div>
                                    <!-- End: description -->

                                </div>
                                <!-- End: description body -->

                            @if(!is_null($auction->terms_description))
                                <!-- Start: features body -->
                                    <div class="tab-pane fade" id="featu" role="tabpanel" aria-labelledby="features">

                                        <!-- Start: features -->
                                        <div class="m-t-50">
                                            <p class="single-blog-details text-justify">
                                                {{view_html($auction->terms_description)}}
                                            </p>
                                        </div>
                                        <!-- End: features -->

                                    </div>
                                    <!-- End: features body -->
                            @endif

                            @auth()
                                <!-- @if($auction->auction_type == AUCTION_TYPE_HIGHEST_BIDDER)
                                    <!-- Start: amenties body --
                                    <div class="tab-pane fade" id="amenti" role="tabpanel" aria-labelledby="amenties">

                                        <!-- Start: amenties --
                                        <div class="m-t-50">
                                            <div class="row">

                                                <!-- Start: amenties list --
                                                <div class="col-12">
                                                    @include('layouts.includes.bidding_list')
                                                </div>
                                                <!-- End: amenties list --

                                            </div>
                                        </div>
                                        <!-- End: amenties --

                                    </div>
                                    <!-- End: amenties body --
                                @endif -->
                            @endauth

                            </div>
                            <!-- End: property tab body -->

                        </div>
                        <!-- End: property tab -->

                        @auth
                        <!-- Start: comment section -->
                        <div class="m-t-50">

                            <!-- Start: total comment -->
                            <div class="single-comment-amount mb-4">
                                {{__('Comments')}}
                            </div>
                            <!-- End: total comment -->

                            <!-- Start: single comment -->
                            @if(count($comments) > 0)
                                @include('layouts.includes.comment_index')
                            @else
                                <span class="color-666">
                                    <h6><i class="fa fa-comment-o"></i> {{('No Comment Available')}}</h6>
                                </span>
                            @endif
                            <!-- End: single comment -->

                            <!-- Start: total comment -->
                            <div class="single-comment-amount text-capitalize mt-5 mb-4">
                                {{__('add comment')}}
                            </div>
                            <!-- End: total comment -->

                            <!-- Start: comment form -->
                        @include('layouts.includes.comment_form')
                        <!-- Start: comment form -->

                        </div>
                        <!-- End: comment section -->
                        @endauth

                    </div>
                    <!-- End: property details body -->
                </div>
                <!-- End: description area -->

            </div>
        </div>
    </div>
    <!-- ::::::::::::::::::::::::END PAGE HEAD ::::::::::::::::::::::::: -->

@endsection

@section('script')
    <script src="{{ asset('frontend/assets/js/owl.carousel.js') }}"></script>
    <script src="{{ asset('js/cvalidator.min.js') }}"></script>
    <script src="{{ asset('vendor/moment.js/moment.min.js') }}"></script>
    <script src="{{ asset('vendor/jasny-bootstrap/js/jasny-bootstrap.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap4-datetimepicker/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script type="text/javascript">
        
        $(document).ready(function () {
            let user = @json(auth()->user());
            $('.cvalidate').cValidate();
            //Init jquery Date Picker
            $('.datepicker').datetimepicker({
                format: 'YYYY-MM-DD',
            });
            $('.toggle').click(function () {
                $('#target').toggle();
            });

            $('#confirmBid').on('click', function() {
                $('#bidForm').submit();
            })

            $('#offer').on('click',function(){
                $('#confirmBidModal').modal("toggle");
            })

            var sync1 = $("#sync1");
            var sync2 = $("#sync2");
            var slidesPerPage = 4; //globaly define number of elements per page
            var syncedSecondary = true;

            sync1.owlCarousel({
                items: 1,
                autoplayTimeout: 7000,
                smartSpeed: 2000,
                nav: false,
                autoplay: true,
                dots: false,
                loop: true,
                responsiveRefreshRate: 200,
            }).on('changed.owl.carousel', syncPosition);

            sync2
                .on('initialized.owl.carousel', function () {
                    sync2.find(".owl-item").eq(0).addClass("current");
                })
                .owlCarousel({
                    items: slidesPerPage,
                    dots: false,
                    nav: false,
                    autoplayTimeout: 7000,
                    smartSpeed: 2000,
                    slideSpeed: 500,
                    slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
                    responsiveRefreshRate: 100
                }).on('changed.owl.carousel', syncPosition2);

            sync2.on("click", ".owl-item", function (e) {
                e.preventDefault();
                var number = $(this).index();
                sync1.data('owl.carousel').to(number, 300, true);
            });

            function syncPosition(el) {
                //if you set loop to false, you have to restore this next line
                //var current = el.item.index;

                //if you disable loop you have to comment this block
                var count = el.item.count - 1;
                var current = Math.round(el.item.index - (el.item.count / 2) - .5);

                if (current < 0) {
                    current = count;
                }
                if (current > count) {
                    current = 0;
                }

                //end block

                sync2
                    .find(".owl-item")
                    .removeClass("current")
                    .eq(current)
                    .addClass("current");
                var onscreen = sync2.find('.owl-item.active').length - 1;
                var start = sync2.find('.owl-item.active').first().index();
                var end = sync2.find('.owl-item.active').last().index();

                if (current > end) {
                    sync2.data('owl.carousel').to(current, 100, true);
                }
                if (current < start) {
                    sync2.data('owl.carousel').to(current - onscreen, 100, true);
                }
            }

            function syncPosition2(el) {
                if (syncedSecondary) {
                    var number = el.item.index;
                    sync1.data('owl.carousel').to(number, 100, true);
                }
            }

            Echo.channel('auction-bid')
                .listen('BroadcastAuctionBid', (response) => {
                    if (response) {
                        let row = '';


                        @if(auth()->check() && !is_null(auth()->user()->seller) ? $auction->seller_id == auth()->user()->seller->id : false)
                            row = row + '<li>' + response.username + '</li>';
                            @endif

                        let myBidHtml = '';
                        if (user.id == response.user_id) {
                            myBidHtml = '<span class="badge-success py-1 px-2 badge-pill fz-10 mr-2">' + "{{ __('My Bid') }}" +'</span>';
                        }

                        row = row + '<li>' +
                            myBidHtml +
                            '<span class="color-default fz-16">' + response.amount + '</span>' +
                            '<span class="fz-12">' + response.currency + '</span>' +
                            '</li>';

                        $('#ul_bid').append(row);

                        $('#count-bid').html(response.bid_count);

                        $('#max-bid').html(`<span class="font-weight-normal"> {{$auction->currency->symbol}}</span> ${response.bigger_bid} </span>`);

                        let minimumBid = response.bigger_bid + response.bid_increment_dif;

                        $('#minimum-bid').html(`<span class="mr-1 font-weight-normal">{{$auction->currency->symbol}}</span> ${minimumBid}</span>`);

                    }
                });
        });
        new Vue({
            el:'#app'
        });
    </script>
@endsection

@section('style-top')
    @include('layouts.includes.list-css')
    <link rel="stylesheet" href="{{asset('frontend/assets/css/owl.carousel.css')}}">
    <link rel="stylesheet" href="{{asset('frontend/assets/css/owl.theme.default.min.css')}}">
    <link rel="stylesheet" href="{{asset('css/table_replace.css')}}">

    <style>

        .agent-info .personal-info ul li span {
            width: 40%;
        }

        .dispute-link {
            right: 10px;
            top: 10px;
            z-index: 99;
            font-size: 14px;
            color: #666;
            border-radius: 40px;
            background: rgba(255, 255, 255, .8);
        }

        .dispute-link a {
            font-size: 14px;
            color: #666;
        }

        .dispute-link .drop-menu.show {
            width: 190px !important;
        }

        #target {
            display: none;
        }

        .Hide {
            display: none;
        }

        .address-dropdown {
            top: 0;
            right: 0;
        }

        .winner-parent {
            position: relative;
            overflow: hidden;
        }

        .winner-image {
            top: -10px;
            right: 40px;
            width: 60px;
            z-index: 999;
        }

        .timer {
            display: flex !important;
            justify-content: center;
        }
    </style>
@endsection
