@extends('frontend.layouts.master')

@section('content')
    @if(!is_null($slider))
        @include('frontend.layouts.includes.auction_slider')
    @endif
    <div class="p-b-100">
        @foreach($layoutViews as $layoutView)
            {{view_html($layoutView)}}
        @endforeach
    </div>
@endsection

@section('style-top')
    <link rel="stylesheet" href="{{ asset('frontend/assets/tamplate/css/owl.carousel.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/assets/css/owl.theme.default.min.css') }}">
@endsection
@section('script')
    <!-- <script src="{{asset('vendor/owl-carousel/owl.carousel.min.js')}}"></script> -->
    <script>
        $(document).ready(function () {
            //$('.carousel').carousel();
            //$('#carouselExampleIndicators').carousel();
        });
        new Vue({
            el:'#app'
        });
    </script>
@endsection
