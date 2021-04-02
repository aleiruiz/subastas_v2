
    {{ Form::open(['route'=>['auction.store'],'class'=>'form-horizontal cvalidate', 'files' => true]) }}
    @method('post')
    @basekey

        <div class="card">
            <div class="card-header py-4">
                <h3 class="font-weight-bold d-inline text-secondary">{{$title}}</h3>
            </div>
            <div class="card-body p-5">

                <!-- Start: auction main content -->
                <div class="form-group form-row">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('main_content') }}">{{('Main Content :')}}</label>

                    <div class="col-lg-10">

                        <!-- Start: currency type -->
                        {{ Form::select(fake_field('currency_id'), $currencies, old('currency_id', null), ['class' => 'custom-select color-666', 'id' => fake_field('currency_id'), 'placeholder' =>  __('Select Currency Type')]) }}
                        <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('currency_id') }}">{{ $errors->first('currency_id') }}</span>
                        <!-- End: currency type -->

                        <!-- Start: auction type -->
                        {{ Form::select(fake_field('auction_type'), auction_type(), old('auction_type', 1), ['class' => 'custom-select color-666 mt-3', 'id' => fake_field('auction_type'), 'style' => 'display: none', 'placeholder' =>  __('Select Auction Type')]) }}
                        <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('auction_type') }}">{{ $errors->first('auction_type') }}</span>
                        <!-- End: auction type -->

                        <!-- Start: category -->
                        {{ Form::select(fake_field('category_id'), $categories, old('category_id', null), ['class' => 'custom-select color-666 mt-3', 'id' => fake_field('category_id'), 'data-cval-name' => 'The Category field', 'placeholder' =>  __('Select Category')]) }}
                        <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('category_id') }}">{{ $errors->first('category_id') }}</span>
                        <!-- End: category -->

                    </div>

                </div>
                <!-- End: auction main content -->

                <!-- Start: auction main content -->
                <div class="form-group form-row mt-4">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('auction_about') }}">{{('About Auction :')}}</label>

                    <div class="col-lg-10">

                        <div class="form-row">
                            <div class="col-12">
                                <!-- Start: title -->
                                {{ Form::text(fake_field('title'), old('title'), ['class'=> 'form-control', 'id' => fake_field('title'),'data-cval-name' => 'The title field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Contest Title')]) }}
                                <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('title') }}">{{ $errors->first('title') }}</span>
                                <!-- End: title -->
                            </div>
                        </div>

                        <!-- Start: bid initial price and bid increment difference -->
                        <div class="form-row mt-3">

                            <!-- Start: bid initial price -->
                            <div class="col-6">
                                {{ Form::text(fake_field('bid_initial_price'), old('bid_initial_price'), ['class'=> 'form-control', 'id' => fake_field('bid_initial_price'),'data-cval-name' => 'The bid_initial_price field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Bid Initial Price')]) }}
                                <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('bid_initial_price') }}">{{ $errors->first('bid_initial_price') }}</span>
                            </div>
                            <!-- End: bid initial price -->

                            <!-- Start: bid increment difference -->
                            <div class="col-6">
                                {{ Form::text(fake_field('bid_increment_dif'), old('bid_increment_dif'), ['class'=> 'form-control', 'id' => fake_field('bid_increment_dif'),'data-cval-name' => 'The bid_increment_dif field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Bid Increment Difference')]) }}
                                <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('bid_increment_dif') }}">{{ $errors->first('bid_increment_dif') }}</span>
                            </div>
                            <!-- End: bid increment difference -->

                            <!-- Start: precio reserva -->
                            <div class="col-6">
                                {{ Form::text(fake_field('reserve_price'), old('reserve_price'), ['class'=> 'form-control', 'id' => fake_field('reserve_price'),'data-cval-name' => 'The reserve_price field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Reserve Price')]) }}
                                <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('reserve_price') }}">{{ $errors->first('reserve_price') }}</span>
                            </div>
                            <!-- End: precio reserva -->


                        </div>
                        <!-- End: bid initial price and bid increment difference -->

                        <!-- Start: starting and ending date -->
                        <div class="form-row mt-3">
                            <div class="col-6">

                                <!-- Start: starting date -->
                                {{ Form::text(fake_field('starting_date'), old('starting_date'), ['class'=> 'form-control datepicker', 'id' => fake_field('starting_date'),'data-cval-name' => 'The starting_date field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Starting Date')]) }}
                                <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('starting_date') }}">{{ $errors->first('starting_date') }}</span>
                                <!-- End: starting date -->

                            </div>
                            <div class="col-6" style="display:none;">
                                <!-- Start: ending date -->
                                {{ Form::text(fake_field('ending_date'), old('ending_date'), ['class'=> 'form-control datepicker', 'id' => fake_field('ending_date'),'data-cval-name' => 'The ending_date field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Ending Date')]) }}
                                <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('ending_date') }}">{{ $errors->first('ending_date') }}</span>
                                <!-- End: ending date -->
                            </div>
                        </div>
                        <!-- End: starting and ending date -->

                    </div>

                </div>
                <!-- End: auction main content -->

                <!-- Start: descriptions -->
                <div class="form-group form-row mt-4">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('main_content') }}">{{('Product Description :')}}</label>

                    <div class="col-lg-10">

                        <!-- Start: description -->
                        {{ Form::textarea(fake_field('product_description'), old('product_description'), ['class'=> 'form-control', 'id' => fake_field('product_description'),'data-cval-name' => 'The product_description field','data-cval-rules' => 'required', 'placeholder' => __('Description'), 'rows' => '3']) }}
                        <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('product_description') }}">{{ $errors->first('product_description') }}</span>
                        <!-- End: description -->

                    </div>

                </div>
                <!-- End: descriptions -->

                <!-- Start: terms descriptions -->
                <div class="form-group form-row mt-4">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('main_content') }}">{{('Terms Description :')}}</label>

                    <div class="col-lg-10">

                        <!-- Start: terms description -->
                        {{ Form::textarea(fake_field('terms_description'), old('terms_description'), ['class'=> 'form-control', 'id' => fake_field('terms_description'),'data-cval-name' => 'The terms_description field','data-cval-rules' => 'required|decimal', 'placeholder' => __('Terms Description'), 'rows' => '3']) }}
                        <span class="invalid-feedback cval-error" data-cval-error="{{ fake_field('terms_description') }}">{{ $errors->first('terms_description') }}</span>
                        <!-- End: terms description -->

                    </div>

                </div>
                <!-- End: terms descriptions -->

                <!-- Start: basic information -->
                <div class="form-group form-row mt-4">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('main_content') }}">{{('Shippable :')}}</label>

                    <div class="col-md-10">
                        <div class="cm-switch">
                            {{ Form::radio(fake_field('is_shippable'), ACTIVE_STATUS_ACTIVE, (string)old('is_shippable', isset($paymentMethod) ? $paymentMethod->is_shippable : ACTIVE_STATUS_ACTIVE) === (string)ACTIVE_STATUS_ACTIVE ? true : false, ['id' => fake_field('is_shippable') . '-active', 'class' => 'cm-switch-input', 'data-cval-name' => 'The active status field', 'data-cval-rules' => 'required|integer|in:' . array_to_string(active_status())]) }}
                            <label for="{{ fake_field('is_shippable') }}-active" class="cm-switch-label">{{ __('Yes') }}</label>

                            {{ Form::radio(fake_field('is_shippable'), ACTIVE_STATUS_INACTIVE, (string)old('is_shippable', isset($paymentMethod) ? $paymentMethod->is_shippable : false) === (string)ACTIVE_STATUS_INACTIVE ? true : false, ['id' => fake_field('is_shippable') . '-inactive', 'class' => 'cm-switch-input']) }}
                            <label for="{{ fake_field('is_shippable') }}-inactive" class="cm-switch-label">{{ __('No') }}</label>
                        </div>
                    </div>
                </div>
                <!-- End: basic information -->

                <!-- Start: basic information -->
                <div class="form-group form-row mt-4">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('content') }}">{{('Shipping Type :')}}</label>

                    <div class="col-md-10">
                        <div class="cm-switch">
                            {{ Form::radio(fake_field('shipping_type'), ACTIVE_STATUS_ACTIVE, (string)old('shipping_type', isset($paymentMethod) ? $paymentMethod->shipping_type : ACTIVE_STATUS_ACTIVE) === (string)ACTIVE_STATUS_ACTIVE ? true : false, ['id' => fake_field('shipping_type') . '-active', 'class' => 'cm-switch-input', 'data-cval-name' => 'The active status field', 'data-cval-rules' => 'required|integer|in:' . array_to_string(active_status())]) }}
                            <label for="{{ fake_field('shipping_type') }}-active" class="cm-switch-label">{{ __('Free') }}</label>

                            {{ Form::radio(fake_field('shipping_type'), ACTIVE_STATUS_INACTIVE, (string)old('shipping_type', isset($paymentMethod) ? $paymentMethod->shipping_type : false) === (string)ACTIVE_STATUS_INACTIVE ? true : false, ['id' => fake_field('shipping_type') . '-inactive', 'class' => 'cm-switch-input']) }}
                            <label for="{{ fake_field('shipping_type') }}-inactive" class="cm-switch-label">{{ __('Paid') }}</label>
                        </div>
                    </div>
                </div>
                <!-- End: basic information -->

                <!-- Start: basic information -->
                <div class="form-group form-row mt-4" style="display:none;">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('content') }}">{{('Multiple Bid :')}}</label>

                    <div class="col-md-10">
                        <div class="cm-switch">
                            {{ Form::radio(fake_field('is_multiple_bid_allowed'), ACTIVE_STATUS_ACTIVE, (string)old('is_multiple_bid_allowed', isset($paymentMethod) ? $paymentMethod->is_multiple_bid_allowed : ACTIVE_STATUS_ACTIVE) === (string)ACTIVE_STATUS_ACTIVE ? true : false, ['id' => fake_field('is_multiple_bid_allowed') . '-active', 'class' => 'cm-switch-input', 'data-cval-name' => 'The active status field', 'data-cval-rules' => 'required|integer|in:' . array_to_string(active_status())]) }}
                            <label for="{{ fake_field('is_multiple_bid_allowed') }}-active" class="cm-switch-label">{{ __('Allowed') }}</label>

                            {{ Form::radio(fake_field('is_multiple_bid_allowed'), ACTIVE_STATUS_INACTIVE, (string)old('is_multiple_bid_allowed', isset($paymentMethod) ? $paymentMethod->is_multiple_bid_allowed : false) === (string)ACTIVE_STATUS_INACTIVE ? true : false, ['id' => fake_field('is_multiple_bid_allowed') . '-inactive', 'class' => 'cm-switch-input']) }}
                            <label for="{{ fake_field('is_multiple_bid_allowed') }}-inactive" class="cm-switch-label">{{ __('Not Allowed') }}</label>
                        </div>
                    </div>
                </div>
                <!-- End: basic information -->

                <!-- Start: product image -->
                <div class="form-group form-row mt-4">
                    <label class="col-lg-2 col-form-label text-right pr-3 color-999" for="{{ fake_field('content') }}">{{('Multiple Image :')}}</label>
                    <div class="col-lg-10">
                        <div id="preview-multi-img">
                            <div class="row" id="TextBoxContainer">
                                <div class="col-lg-4">
                                    <div class="fileinput fileinput-new" data-provides="fileinput">
                                        <div class="fileinput fileinput-new" data-provides="fileinput">
                                            <div class="fileinput-new img-thumbnail mb-3">
                                                <img class="img" src="{{know_your_customer_images('preview.png')}}"  alt="">
                                            </div>
                                            <div class="fileinput-preview fileinput-exists mb-3 img-thumbnail"></div>
                                            <div>
                                                <span class="btn btn-sm btn-outline-success btn-file mr-2">
                                                    <span class="fileinput-new">Select</span>
                                                    <span class="fileinput-exists">Change</span>
                                                    {{ Form::file('images[]', [old('images'),'class'=>'multi-input', 'id' => fake_field('images'),])}}
                                                </span>
                                                <a href="#" class="btn btn-sm btn-outline-danger fileinput-exists" data-dismiss="fileinput">Remove</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button id="btnAdd" type="button" class="btn btn-primary mt-3" data-toggle="tooltip">{{__('Add Image')}}</button>
                        </div>
                    </div>
                </div>
                <!-- End: product image -->

            </div>

            <div class="card-footer text-muted">
                <button value="Submit Design" type="submit" class="btn custom-btn float-right has-spinner my-2" id="two">{{__('Create Auction')}}</button>
            </div>

        </div>

    {{ Form::close() }}

