<!-- Start: comment form -->
@component('components.card',['type' => 'info', 'class' => 'card text-right mt-4', 'style' => 'overflow-y: scroll; height: 400px;'])


        

        <ul id="ul_bid">
        @foreach($list['items']->reverse() as $bid)
            @if(!is_null(auth()->user()->seller) ? $auction->seller_id == auth()->user()->seller->id : false)

            @endif
            <li>
                
                @if($bid->user_id == auth()->id())
                    <img src="{{asset('images/has-ofertado.svg')}}" alt="">
                    <strong>{{$bid->user->username}}</strong>
                    <span class="gris-color">has ofertado</span>
                    <span class="color-default fz-16">{{$bid->amount}}</span>
                    <span class="fz-12">{{!is_null($auction->currency) ? $auction->currency->symbol : ''}}</span>
                
                @else
                    <span class="usuario-batalla">{{$bid->user->username}}</span>
                    <span class="gris-color">ha ofertado por</span>
                    <span class="color-default fz-16">{{$bid->amount}}</span>
                    <span class="fz-12">{{!is_null($auction->currency) ? $auction->currency->symbol : ''}}</span>   
                    <img src="{{asset('images/has-ofertado-naranja.svg')}}" alt="">
                @endif
            </li>
        @endforeach
        </ul>

@endcomponent

