<!-- Start: comment form -->
@component('components.card',['type' => 'info', 'class' => 'card text-right mt-4', 'style' => 'overflow-y: scroll; height: 100px;'])


        

        <ul id="ul_bid">
        @foreach($list['items']->reverse() as $bid)
            @if(!is_null(auth()->user()->seller) ? $auction->seller_id == auth()->user()->seller->id : false)
                <li>{{$bid->user->username}}</li>
            @endif
            <li>
                @if($bid->user_id == auth()->id())
                    <span class="badge-success py-1 px-2 badge-pill fz-10 mr-2">{{ __('My Bid') }}</span>
                @endif
                <span class="color-default fz-16">{{$bid->amount}}</span>
                <span class="fz-12">{{!is_null($auction->currency) ? $auction->currency->symbol : ''}}</span>
            </li>
        @endforeach
        </ul>

@endcomponent

