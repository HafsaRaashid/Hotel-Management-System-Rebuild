{{--
    BL-018 - Walk-In Availability List. Functional-only: no UI fidelity work
    has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Walk-In - Available Rooms')

@section('content')
    @include('partials.admin-nav')

    <main class="container py-5">
        <h1>Available Rooms</h1>

        <table class="table">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Category</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->room }}</td>
                        <td>{{ $room->category->name }}</td>
                        <td>
                            <a href="{{ route('walk-in.create', ['room_id' => $room->id]) }}" class="btn btn-sm btn-primary">Check In</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
@endsection
