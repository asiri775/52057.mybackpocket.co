@extends('layouts.common_home')
@section('head')
    <link href="{{ asset('dist/frontend/module/space/css/space.css?_ver='.config('app.version')) }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/ion_rangeslider/css/ion.rangeSlider.min.css") }}"/>
    <link rel="stylesheet" type="text/css" href="{{ asset("libs/fotorama/fotorama.css") }}"/>

@endsection
@section('content')
    <div class="layout1 bravo_wrap">
        <div id="msg-success" align="left" class="alert alert-success" style="display:none;"></div>

        <div class=" container-fixed-lg">
            <div class="bravo_detail_hotel">
                @include('Space::frontend.layouts.details.space-banner')
                <div class="bravo_content"> 
                    <div class="container">
                        <div class="row">
                            <div class="col-md-8 col-lg-8 mb-5 page-template-content">
                                @php $review_score = $row->review_data @endphp
                                @include('Space::frontend.layouts.details.space-detail')
                                {{-- @include('Space::frontend.layouts.details.space-related') --}}
                            </div>
                            <div class="col-md-4 col-lg-4 mb-5">
                                @include('Space::frontend.layouts.details.space-form-book')
                               
                                <div class="clearfix"></div>
                                @include('Tour::frontend.layouts.details.vendor')

                                <div class="clearfix"></div>
                                <div class="add-btn mt-1 mb-3">
                                    @php 
                                        $favourite = App\Models\AddToFavourite::where('user_id', Auth::id())->where('object_id', $row->id)->first();
                                    @endphp
                                    <a href="javascript:void(0)" data-id="{{$row->id}}"
                                       onclick="addToFavourite(event.target)"
                                       @if($favourite != '') style="pointer-events: none" @endif class="btn btn-large">
                                        @if($favourite != '')Added To Favourites
                                        @else Add To Favourites
                                        @endif
                                    </a>
                                    <span id="success-message" style="color: green;"></span>
                                </div>

                            </div>
                        </div> 
                        @if(count($relatedSpaces) > 0)
                        <div class="row end_tour_sticky nearby-spaces-section">
                            <div class="col-md-12 bravo_search_space">
                                @if($relatedSpaces)
                                    <div class="g-itinerary bravo-list-item">
                                        <h3> {{__("Spaces Nearby")}} </h3>
                                        <div class="list-item owl-carousel nearby-spaces">
                                            @foreach($relatedSpaces as $relatedSpace)
                                            <?php
                                            $row = $relatedSpace;
                                            ?>
                                                <div class="item">
                                                    @include('Space::frontend.layouts.search.loop-gird')
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @include('Space::frontend.layouts.details.space-form-book-mobile')
            </div>

            <script>
                function addToFavourite(event) {
                    var space_id = $(event).data("id");

                    let _url = "{{\App\Helpers\CodeHelper::withAppUrl('/space/add-to-favourite')}}";
                    let _token = $('meta[name="csrf-token"]').attr('content');


                    $.ajax({
                        url: _url,
                        type: "POST",
                        data: {
                            space_id: space_id,
                            _token: _token
                        },
                        success: function (response) {
                            $('#success-message').append(response.success);
                        }
                    });
                }
            </script>

            @endsection

            @section('footer')
                {!! App\Helpers\MapEngine::scripts() !!}
                <script>
                    jQuery(function ($) {
                        @if($row->map_lat && $row->map_lng)
                        new BravoMapEngine('map_content', {
                            disableScripts: true,
                            fitBounds: true,
                            center: [{{$row->map_lat}}, {{$row->map_lng}}],
                            zoom: {{$row->map_zoom ?? "8"}},
                            ready: function (engineMap) {
                                engineMap.addMarker([{{$row->map_lat}}, {{$row->map_lng}}], {
                                    icon_options: {
                                        iconUrl: "{{get_file_url(setting_item("space_icon_marker_map"),'full') ?? url('images/myoffice-marker-1.png') }}"
                                    }
                                });
                            } 
                        });
                        @endif
                    })
                </script>
                <script>
                    var bravo_booking_data = {!! json_encode($booking_data) !!}
                        var
                    bravo_booking_i18n = {
                        no_date_select: '{{__('Please select Start and End date')}}',
                        no_guest_select: '{{__('Please select at least one guest')}}',
                        load_dates_url: '{{route('space.vendor.availability.loadDates')}}',
                        name_required: '{{ __("Name is Required") }}',
                        email_required: '{{ __("Email is Required") }}',
                    };
                </script>
                <script type="text/javascript"
                        src="{{ asset("libs/ion_rangeslider/js/ion.rangeSlider.min.js") }}"></script>
                <script type="text/javascript" src="{{ asset("libs/fotorama/fotorama.js") }}"></script>
                <script type="text/javascript" src="{{ asset("libs/sticky/jquery.sticky.js") }}"></script>
                <script type="text/javascript"
                        src="{{ asset('module/space/js/single-space.js?_ver='.config('app.version')) }}"></script>
@endsection
