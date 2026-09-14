{{--
    BL-022 - Edit In-Progress Stay's Checkout Date. Functional-only: no UI
    fidelity work has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Edit Stay — ' . $booking->ref_no)

@section('content')
    <main class="container py-5">
        <h1>Edit Stay — Booking #{{ $booking->ref_no }}</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('stays.update', $booking) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="dateout" class="form-label">Check-out date</label>
                <input type="date" class="form-control" id="dateout" name="dateout" value="{{ old('dateout', $booking->dateout) }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </main>
@endsection
