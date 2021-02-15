<!-- Start: comment form -->
@component('components.card',['type' => 'info', 'class' => 'card text-right mt-4', 'style' => 'overflow-y: scroll; height: 400px;'])


        

        <ul id="ul_bid">
        @foreach($list['items']->reverse() as $bid)
            @if(!is_null(auth()->user()->seller) ? $auction->seller_id == auth()->user()->seller->id : false)
                <li>{{$bid->user->username}}</li>
            @endif
            <li style="height:30px !important;">
                
                @if($bid->user_id == auth()->id())
                    <span style="background: url('{{asset('images/winner-badge.png')}}');background-size: 30px 30px;background-repeat: no-repeat;padding-left: 70px;"></span>
                    <strong>{{$bid->user->username}}</strong>
                    <span>Has ofertado</span>
                    <span class="color-default fz-16">{{$bid->amount}}</span>
                    <span class="fz-12">{{!is_null($auction->currency) ? $auction->currency->symbol : ''}}</span>
                
                @else
                    <span>{{$bid->user->username}}</span>
                    <span>Ha ofertado</span>
                    <span class="color-default fz-16">{{$bid->amount}}</span>
                    <span style="padding-right: 40px;" class="fz-12">{{!is_null($auction->currency) ? $auction->currency->symbol : ''}}</span>   
                    <span style="background: url('{{asset('images/winner-badge.png')}}');background-size: 30px 30px;background-repeat: no-repeat;padding-left: 30px;"></span>
                @endif
            </li>
        @endforeach
        </ul>

@endcomponent

