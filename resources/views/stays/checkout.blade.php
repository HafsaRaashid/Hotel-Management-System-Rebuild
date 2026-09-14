{{--
    BL-021 - Guest Check-Out & Billing. Functional-only: no UI fidelity work
    has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Check Out — ' . $booking->ref_no)

@section('content')
    <main class="container py-5">
        <h1>Check Out — Booking #{{ $booking->ref_no }}</h1>

        <dl class="row">
            <dt class="col-sm-3">Room</dt>
            <dd class="col-sm-9">{{ $booking->room->room }}</dd>
            <dt class="col-sm-3">Category</dt>
            <dd class="col-sm-9">{{ $booking->category->name }}</dd>
            <dt class="col-sm-3">Price / night</dt>
            <dd class="col-sm-9">{{ $booking->category->price }}</dd>
            <dt class="col-sm-3">Reference No</dt>
            <dd class="col-sm-9">{{ $booking->ref_no }}</dd>
            <dt class="col-sm-3">Check-in</dt>
            <dd class="col-sm-9">{{ $booking->datein }}</dd>
            <dt class="col-sm-3">Check-out</dt>
            <dd class="col-sm-9">{{ $booking->dateout }}</dd>
            <dt class="col-sm-3">Days</dt>
            <dd class="col-sm-9">{{ $days }}</dd>
            <dt class="col-sm-3">Amount Due</dt>
            <dd class="col-sm-9">{{ $amountDue }}</dd>
        </dl>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stays.checkout', $booking) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="payment" class="form-label">Payment</label>
                <input type="number" min="{{ $amountDue }}" class="form-control" id="payment" name="payment" value="{{ old('payment', $amountDue) }}">
            </div>

            <button type="submit" class="btn btn-primary">Payment &amp; Check Out</button>
        </form>
    </main>
@endsection
