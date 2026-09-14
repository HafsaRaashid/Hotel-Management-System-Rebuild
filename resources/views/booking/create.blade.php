{{--
    BL-015 - Public Reservation Intake. Functional-only: no UI fidelity work
    has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Book a Room')

@section('content')
    @include('partials.marketing-nav')

    <main class="container py-4">
        <h1>Book a Room</h1>

        @if (session('ref_no'))
            <div class="alert alert-success">Booking confirmed. Your reference number is {{ session('ref_no') }}.</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('booking.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label for="mail" class="form-label">Mail</label>
                <input type="email" class="form-control" id="mail" name="mail" value="{{ old('mail') }}">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" inputmode="numeric" pattern="[0-9]{7,15}" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Room Type</label>
                <select class="form-select" id="category_id" name="category_id">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="adult" class="form-label">Adults</label>
                <input type="number" min="0" class="form-control" id="adult" name="adult" value="{{ old('adult', 1) }}">
            </div>

            <div class="mb-3">
                <label for="children" class="form-label">Children</label>
                <input type="number" min="0" class="form-control" id="children" name="children" value="{{ old('children', 0) }}">
            </div>

            <div class="mb-3">
                <label for="datein" class="form-label">Check-in date</label>
                <input type="date" class="form-control" id="datein" name="datein" value="{{ old('datein') }}">
            </div>

            <div class="mb-3">
                <label for="dateout" class="form-label">Check-out date</label>
                <input type="date" class="form-control" id="dateout" name="dateout" value="{{ old('dateout') }}">
            </div>

            <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message">{{ old('message') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </main>
@endsection
