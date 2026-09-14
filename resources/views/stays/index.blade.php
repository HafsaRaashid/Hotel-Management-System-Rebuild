{{--
    BL-020 - Check-In/Out List. Functional-only: no UI fidelity work has
    been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Stays')

@section('content')
    @include('partials.admin-nav')

    <main class="container py-5">
        <h1>Stays</h1>

        @if (session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        <table class="table">
            <thead>
                <tr>
                    <th>Ref No</th>
                    <th>Name</th>
                    <th>Room</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($stays as $stay)
                    <tr>
                        <td>{{ $stay->ref_no }}</td>
                        <td>{{ $stay->name }}</td>
                        <td>{{ $stay->room?->room }}</td>
                        <td>{{ $stay->status === 1 ? 'Checked In' : 'Checked Out' }}</td>
                        <td>
                            @if ($stay->status === 1)
                                <a href="{{ route('stays.checkout.show', $stay) }}" class="btn btn-sm btn-primary">Check Out</a>
                                <a href="{{ route('stays.edit', $stay) }}" class="btn btn-sm btn-secondary">Edit</a>
                            @else
                                <a href="{{ route('stays.show', $stay) }}" class="btn btn-sm btn-secondary">View</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
@endsection
