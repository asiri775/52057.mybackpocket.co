@extends('layouts.new_user')
@section('content')
    <style>
        .week-dragger {
            /* display: none; */
        }

        .months-drager {
            /* display: none; */
        }

        .fresha-calendar-card {
            background: #fff;
            padding-bottom: 60px;
        }

        .calendar .options {
            margin-top: 0;
            padding-top: 25px;
        }

        .calendar .calendar-container .view .tble .trow .tcell {
            padding: 0;
        }

        .calendar .calendar-container .view .tble .thead .tcell .weekdate {
            font-size: 24px;
        }

        .calendar .calendar-container .view .tble .thead .tcell:before {
            width: 100%;
        }

        .calendar .calendar-container .view .tble .thead .tcell {
            padding: 10px 0;
        }

        .fc-header-toolbar.fc-toolbar.fc-toolbar-ltr {
            padding: 0 15px;
            padding-top: 15px;
            display: none;
        }

        .calendar-header {
            display: flex;
            align-content: center;
            justify-content: center;
            padding: 15px;
        }

        .calendar-header .left {
            margin-left: 0;
            margin-right: auto;
        }

        .calendar-header .center {
            margin-left: auto;
            margin-right: auto;
            display: flex;
            align-content: center;
            justify-content: center;
            border: 1px solid #dedede;
            border-radius: 50px;
            overflow: hidden;
        }

        .calendar-header .center h5,
        .calendar-header .center a {
            padding: 5px 15px;
            margin: 0;
            border-right: 1px solid #dedede;
            font-size: 14px;
            color: #333;
            line-height: 28px;
        }

        .calendar-header .right {
            margin-left: auto;
            margin-right: 0;
        }

        #runningDate {
            position: relative;
        }

        .datepicker-cal {
            opacity: 0;;
            position: absolute;
            top: 0;
            z-index: -1;
            left: 0;
        }
    </style>

    <div class="fresha-calendar-card">
        <div class="calendar-header">
            <div class="left">
                <form id="spaceEarningForm" action="<?= route('user.calendar') ?>" method="get">
                    <select name="id" id="spaceEarningFormField" class="form-control" required>
                        <option value="">Select Space</option>
                        <?php
                            foreach($userSpaces as $userSpacesItem){
                                ?>
                        <option <?php if ($userSpacesItem->id == $id) {
                            echo 'selected';
                        } ?> value="<?= $userSpacesItem->id ?>">
                            <?= $userSpacesItem->title ?></option>
                        <?php
                            }
                            ?>
                    </select>
                </form>
            </div>
            <div class="center">
                <a href="javascript:;" id="goPrev">
                    <i class="fa fa-chevron-left"></i>
                </a>
                <a href="javascript:;" id="goToday">Today</a>
                <h5 id="runningDate">
                    <span class="dateThing">-</span>
                    <div class="datepicker-cal">
                        <div class="input-group date col-md-12 p-l-0 date-picker-single-component">
                            <input autocomplete="off" type="text"
                                class="form-control datepicker-cal-select from filterField" name="to"
                                id="singlebooking-todate">
                            <div class="input-group-append ">
                                <span class="input-group-text"><i class="pg-icon">calendar</i></span>
                            </div>
                        </div>
                    </div>
                </h5>
                <a href="javascript:;" id="goNext">
                    <i class="fa fa-chevron-right"></i>
                </a>
            </div>
            <div class="right">
                <select name="id" id="calendarView" class="form-control" required>
                    <option value="week">Week</option>
                    <option value="day">Day</option>
                    <option value="month">Month</option>
                </select>
            </div>
        </div>
        <div id='availabilityTimeCalendar'></div>
    </div>
@endsection

@section('footer')
    <script type="text/javascript" src="{{ asset('libs/tinymce/js/tinymce/tinymce.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/condition.js?_ver=' . config('app.version')) }}"></script>
    <script type="text/javascript" src="{{ url('module/core/js/map-engine.js?_ver=' . config('app.version')) }}"></script>

    {!! App\Helpers\MapEngine::scripts() !!}

    <script>
        let availabilityTimeCalendar = null;

        function showCalendarModal() {
            $("#availabilityCalendar").modal("show");
            availabilityTimeCalendar = new FullCalendar.Calendar(document.getElementById('availabilityTimeCalendar'), {
                eventSources: [{
                    url: '{{ route('space.vendor.availability.calendarAppointments') }}?id={{ $id }}',
                }],
                initialView: 'timeGridWeek',
                dayMaxEvents: true,
                navLinks: true,
                headerToolbar: {
                    left: '',
                    center: 'prev,today,title,next',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                datesSet: printCalendarDate,
            });
            availabilityTimeCalendar.render();
        }

        showCalendarModal();

        $(document).on("change", "#spaceEarningFormField", function() {
            $("#spaceEarningForm").submit();
        });

        function printCalendarDate() {
            $("#runningDate span.dateThing").html(availabilityTimeCalendar.currentData.viewTitle);
        }

        $(document).on("click", "#goNext", function() {
            availabilityTimeCalendar.next();
        });

        $(document).on("click", "#goToday", function() {
            availabilityTimeCalendar.today();
        });

        $(document).on("click", "#goPrev", function() {
            availabilityTimeCalendar.prev();
        });

        $(document).on("change", "#calendarView", function() {
            switch ($(this).val()) {
                case 'week':
                    availabilityTimeCalendar.changeView('timeGridWeek');
                    break;
                case 'day':
                    availabilityTimeCalendar.changeView('timeGridDay');
                    break;
                case 'month':
                    availabilityTimeCalendar.changeView('dayGridMonth');
                    break;
            }
        });

        setTimeout(() => {

            $(document).on("change", ".datepicker-cal-select", function() {
                var obj = $(this);
                var date = moment(obj.val()).format("YYYY-MM-DD");
                availabilityTimeCalendar.gotoDate(date);
            });

            $(document).on("click", "#runningDate span.dateThing", function() {
                console.log("fsdf");
                $(".datepicker-cal-select").datepicker("show");
            });

        }, 1000);
    </script>
@endsection
