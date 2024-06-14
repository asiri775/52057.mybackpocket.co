@extends('Email::layout')
@section('content')

    <div class="b-container">
        <div class="b-panel">
            <h3 class="email-headline"><strong>{{__('Hello Administrator')}}</strong></h3>
            <p>{{__('Here are new contact information:')}}</p>
            <br>
            <div class="b-panel">
                <div class="b-table-wrap">
                    <table class="b-table" cellspacing="0" cellpadding="0">
                        <tr class="info-first-name">
                            <td class="label">{{__('Topic')}}</td>
                            <td class="val">{{$contact->topic}}</td>
                        </tr>
                        <tr class="info-first-name">
                            <td class="label">{{__('Subject')}}</td>
                            <td class="val">{{$contact->subject}}</td>
                        </tr>
                        <tr class="info-first-name">
                            <td class="label">{{__('Message')}}</td>
                            <td class="val">{{$contact->message}}</td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
