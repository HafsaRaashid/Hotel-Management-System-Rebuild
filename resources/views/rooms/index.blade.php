{{--
    BL-002 - Room List & Category Filter. BL-004 (Room Delete) landed in
    change 006-delete-referential-integrity. Functional-only: no UI fidelity
    work has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Rooms')

@section('content')
    <main class="container py-5">
        <h1>Rooms</h1>

        @if (session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        <a href="{{ route('rooms.create') }}" class="btn btn-primary mb-3">Add Room</a>

        <form method="GET" action="{{ route('rooms.index') }}" class="row g-2 mb-3">
            <div class="col-auto">
                <select name="category_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $selectedCategoryId === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Room</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rooms as $room)
                    <tr>
                        <td>{{ $room->room }}</td>
                        <td>{{ $room->category->name }}</td>
                        <td>{{ $room->status === 0 ? 'Available' : 'Unavailable' }}</td>
                        <td>
                            <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </main>
@endsection
