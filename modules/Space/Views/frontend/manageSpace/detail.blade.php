@extends('layouts.yellow_user')
@section('head')
@endsection
@section('content')
    <?php
    if (!$row->id) {
        //new record
        $row->enable_extra_price = 1;
        $row->extra_price = [
            [
                'name' => 'Cleaning Fee',
                'price' => 0,
                'type' => 'one_time',
            ],
        ];
    }
    ?>

    <div class="content sm-gutter">
        <!-- START BREADCRUMBS-->
        <div class="bg-white">
            <div class="container-fluid pl-5">
                <ol class="breadcrumb breadcrumb-alt bg-white mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('user.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('space.vendor.index') }}">Spaces</a></li>
                    <li class="breadcrumb-item active">{{ $row->id ? __('Edit: ') . $row->title : __('Add new space') }}</li>
                </ol>
            </div>
        </div>

        <div class="container-fluid px-5 pt-4 pb-5">

            @if ($row->id)
                @include('Language::admin.navigation')
            @endif

            <div class="lang-content-box">
                @include('admin.message')
                <form
                    action="{{ route('space.vendor.store', ['id' => $row->id ? $row->id : '-1', 'lang' => request()->query('lang')]) }}"
                    method="post">
                    @csrf
                    <div class="form-add-service">

                        <div class="nav nav-tabs nav-fill" id="nav-tab" role="tablist">
                            <a data-toggle="tab" href="#nav-content" aria-selected="true"
                                class="active">{{ __('1. Content') }}</a>
                            <a data-toggle="tab" href="#nav-media" aria-selected="false">{{ __('2. Media') }}</a>
                            <a data-toggle="tab" href="#nav-pricing" aria-selected="false">{{ __('3. Pricing') }}</a>
                            <a data-toggle="tab" href="#nav-amenities" aria-selected="false">{{ __('4. Amenities') }}</a>
                            <a data-toggle="tab" href="#nav-calendar" aria-selected="false">{{ __('5. Calendar') }}</a>
                            <a data-toggle="tab" href="#nav-legal" aria-selected="false">{{ __('6. Legals') }}</a>
                            <a data-toggle="tab" href="#nav-checkin" aria-selected="false">{{ __('7. Check IN/OUT') }}</a>

                        </div>

                        <div class="tab-content" id="nav-tabContent">

                            <div class="tab-pane fade show active" id="nav-content">
                                @include('Space::admin/space/user/content')
                            </div>
                            <div class="tab-pane fade" id="nav-media">
                                @include('Space::admin/space/user/media')
                            </div>
                            <div class="tab-pane fade" id="nav-pricing">
                                @include('Space::admin/space/user/pricing')
                            </div>
                            <div class="tab-pane fade" id="nav-amenities">
                                @include('Space::admin/space/user/amenities')
                            </div>
                            <div class="tab-pane fade" id="nav-calendar">
                                @include('Space::admin/space/user/calendar')
                            </div>
                            <div class="tab-pane fade" id="nav-legal">
                                @include('Space::admin/space/user/legal')
                            </div>
                            <div class="tab-pane fade" id="nav-checkin">
                                @include('Space::admin/space/user/checkin_out')
                            </div>

                        </div>
                    </div>
                    <div class="d-flex justify-content-between mb-5">
                        <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i>
                            {{ __('Save Changes') }}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection

@section('footer')
    <script type="text/javascript" src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/condition.js?_ver=' . config('app.version')) }}"></script>
    <script type="text/javascript" src="{{ url('module/core/js/map-engine.js?_ver=' . config('app.version')) }}"></script>
    {!! App\Helpers\MapEngine::scripts() !!}
    <script>
        jQuery(function($) {
            new BravoMapEngine('map_content', {
                fitBounds: true,
                center: [{{ $row->map_lat ?? setting_item('map_lat_default') }},
                    {{ $row->map_lng ?? setting_item('map_lng_default') }}
                ],
                zoom: {{ $row->map_zoom ?? '8' }},
                ready: function(engineMap) {
                    @if ($row->map_lat && $row->map_lng)
                        engineMap.addMarker([{{ $row->map_lat }}, {{ $row->map_lng }}], {
                            icon_options: {}
                        });
                    @endif
                    engineMap.on('click', function(dataLatLng) {
                        engineMap.clearMarkers();
                        engineMap.addMarker(dataLatLng, {
                            icon_options: {}
                        });
                        $("input[name=map_lat]").attr("value", dataLatLng[0]);
                        $("input[name=map_lng]").attr("value", dataLatLng[1]);
                    });
                    engineMap.on('zoom_changed', function(zoom) {
                        $("input[name=map_zoom]").attr("value", zoom);
                    });
                    if (bookingCore.map_provider === "gmap") {
                        engineMap.searchBox($('#customPlaceAddress'), function(dataLatLng) {
                            engineMap.clearMarkers();
                            engineMap.addMarker(dataLatLng, {
                                icon_options: {}
                            });
                            $("input[name=map_lat]").attr("value", dataLatLng[0]);
                            $("input[name=map_lng]").attr("value", dataLatLng[1]);
                        });
                    }
                    engineMap.searchBox($('.bravo_searchbox'), function(dataLatLng) {
                        engineMap.clearMarkers();
                        engineMap.addMarker(dataLatLng, {
                            icon_options: {}
                        });
                        $("input[name=map_lat]").attr("value", dataLatLng[0]);
                        $("input[name=map_lng]").attr("value", dataLatLng[1]);
                    });
                }
            });
        })
    </script>

    <script>
        let spaceSettings = {!! json_encode($spaceSettings) !!};

        $(document).on("click", "#loadDefaultFaqs", function() {
            let faqs = JSON.parse(spaceSettings.space_default_faqs);
            for (let faqItem of faqs) {
                $("#addMoreFaq").click();
                let lastFaqItem = $("#spaceFaqLists .g-items").find(".item").last();
                lastFaqItem.find(".title").val(faqItem.title);
                lastFaqItem.find(".content").val(faqItem.content);
            }
        });

        $(document).on("click", ".faq-accord-items .item, .faq-accord-items .item *", function(event) {
            var targetElement = event.target;
            if ($(targetElement).is('input, textarea')) {
                // Logic for input or textarea click
                $(".faq-accord-items .item").removeClass("show");
                $(this).closest(".item").addClass("show");
            } else {
                $(".faq-accord-items .item").removeClass("show");
            }
        });


        $(document).on("click", "#loadDefaultHouseRules", function() {
            tinymce.get('spaceHouseRules').setContent(spaceSettings.space_default_house_rules);
        });

        $(document).on("click", "#loadDefaultTerms", function() {
            tinymce.get('spaceTerms').setContent(spaceSettings.space_default_terms);
        });
    </script>
@endsection
