<!-- Start: main banner -->
<section class="p-t-50 pb-0">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
                    <ol class="carousel-indicators"> 
                        @foreach($slider->images as $key=>$image)
                            <li data-target="#carouselExampleIndicators" data-slide-to="{{$key}}" class="@if($key == 0) active @endif"></li>
                        @endforeach
                      </ol>
                        @foreach($slider->images as $key=>$image)
                            <div class="carousel-item @if($key == 0) active @endif">
                                <img class="d-block w-100" src="{{slider_images($image)}}" alt="preview">
                            </div>
                        @endforeach
                    </div>
                    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="sr-only">Previous</span>
                      </a>
                      <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="sr-only">Next</span>
                      </a>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End: main banner -->
