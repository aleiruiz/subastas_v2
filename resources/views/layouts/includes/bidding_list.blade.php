<!-- Start: comment form -->
@component('components.card',['id' => 'div_bidding_list', 'type' => 'info', 'class' => 'card text-right mt-4', 'style' => 'overflow-y: scroll; height: 400px;display: flex;flex-direction: column-reverse;'])
@php($ind = 0)
@php($mount = 0)

        

        <ul id="ul_bid">
        
            <li id="ya-puedes-enviar">
                <img src="{{asset('images/conteo-logo.svg')}}" alt="">
                <strong>¡Ya puedes enviar tus propuestas!</strong>
            </li>

        @foreach($list['items']->reverse() as $bid)
            @if(!is_null(auth()->user()->seller) ? $auction->seller_id == auth()->user()->seller->id : false)

            @endif
            <li>
                @php($mount = $bid->amount)
                @if($bid->user_id == auth()->id())
                    @php($ind = 1)
                    <img src="{{asset('images/has-ofertado.svg')}}" alt="">
                    <strong>{{$bid->user->username}}</strong>
                    <span class="gris-color">has ofertado</span>
                    <span class="color-default fz-16">{{$bid->amount}}</span>
                    <span class="fz-12">{{!is_null($auction->currency) ? $auction->currency->symbol : ''}}</span>
                
                @else
                    @php($ind = 2)
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

@section('script_add_timer')
<script>
        var timer_glob  = null;
</script>
    @if($ind > 0 && $auction->status == AUCTION_STATUS_RUNNING)
    <script>
        $(document).ready(function () {
            var cntInter = 0;
            strInterval = ["¡A la una!", "¡A las dos!", "¡A las tres!"];

            timer_glob = setInterval(function(){
                cntInter++;
                
                let row = '<li class="li-blue">' +
                    '<span class="color-default fz-16">{{($ind == 1) ? "Te lo llevas" : "Se lo llevan"}} por {{ $mount }}</span>' +
                    '<span class="fz-12"></span>' +
                    '</li>';
                $('#ul_bid').append(row);
                
                let row_msj = '<li class="li-orange">' +
                    '<span class="color-default fz-16">' + strInterval[cntInter - 1] + '</span>' +
                    '<span class="fz-12"></span>' +
                    '</li>';
                $('#ul_bid').append(row_msj);
            
                
                if(cntInter == 3){
                    cntInter = 0;

                    // $("#div_bidding_list").css("display","none");
                    // $("#div_form_bid").css("display","none");
                    // $("#div_info_bid").css("display","block");
                    // $("#div_wait").css("display","block");

                    clearInterval(timer_glob);
                    // setTimeout(function(){
                    //     window.location.reload();
                    // }, 5000);
                }

            }, {{TIME_INTERVAL_AUCTION}});
        });
    </script>
    @endif
@endsection