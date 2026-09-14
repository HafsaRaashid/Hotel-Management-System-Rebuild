{{--
    BL-023 - View Completed Stay Detail. Read-only. Functional-only: no UI
    fidelity work has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Stay — ' . $booking->ref_no)

@section('content')
    <main class="container py-5">
        <h1>Stay — Booking #{{ $booking->ref_no }}</h1>

        <dl class="row">
            <dt class="col-sm-3">Room</dt>
            <dd class="col-sm-9">{{ $booking->room?->room }}</dd>
            <dt class="col-sm-3">Category</dt>
            <dd class="col-sm-9">{{ $booking->category->name }}</dd>
            <dt class="col-sm-3">Price</dt>
            <dd class="col-sm-9">{{ $booking->price }}</dd>
            <dt class="col-sm-3">Reference No</dt>
            <dd class="col-sm-9">{{ $booking->ref_no }}</dd>
            <dt class="col-sm-3">Guest</dt>
            <dd class="col-sm-9">{{ $booking->name }}</dd>
            <dt class="col-sm-3">Phone</dt>
            <dd class="col-sm-9">{{ $booking->phone }}</dd>
            <dt class="col-sm-3">Check-in</dt>
            <dd class="col-sm-9">{{ $booking->datein }}</dd>
            <dt class="col-sm-3">Check-out</dt>
            <dd class="col-sm-9">{{ $booking->dateout }}</dd>
            <dt class="col-sm-3">Days</dt>
            <dd class="col-sm-9">{{ $booking->days_of_stay }}</dd>
        </dl>
    </main>
@endsection
