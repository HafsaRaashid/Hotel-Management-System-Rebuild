{{--
    BL-016 - Pending Bookings List & Cancellation. Functional-only: no UI
    fidelity work has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Pending Bookings')

@section('content')
    <main class="container py-5">
        <h1>Pending Bookings</h1>

        <table class="table">
            <thead>
                <tr>
                    <th>Ref No</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->ref_no }}</td>
                        <td>{{ $booking->name }}</td>
                        <td>{{ $booking->category->name }}</td>
                        <td>{{ $booking->datein }}</td>
                        <td>{{ $booking->dateout }}</td>
                        <td>
                            <a href="{{ route('bookings.pending.convert.show', $booking) }}" class="btn btn-sm btn-primary">Check In</a>
                            <form action="{{ route('bookings.pending.destroy', $booking) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
@endsection
