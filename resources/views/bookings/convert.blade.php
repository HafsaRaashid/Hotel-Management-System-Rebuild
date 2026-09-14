{{--
    BL-017 - Booking to Check-In Conversion. Functional-only: no UI fidelity
    work has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Check In Booking #' . $booking->ref_no)

@section('content')
    <main class="container py-5">
        <h1>Check In — Booking #{{ $booking->ref_no }}</h1>
        <p>{{ $booking->name }} — {{ $booking->category->name }}</p>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('bookings.pending.convert', $booking) }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="room_id" class="form-label">Assign Room</label>
                <select class="form-select" id="room_id" name="room_id">
                    @foreach ($rooms as $room)
                        <option value="{{ $room->id }}">{{ $room->room }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Check In</button>
        </form>
    </main>
@endsection
