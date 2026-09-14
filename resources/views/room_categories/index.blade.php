{{--
    BL-005 - Room Category Management. New capability (CQ-007) - the legacy
    app has no admin screen for this at all. Functional-only: no UI fidelity
    work has been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Room Categories')

@section('content')
    <main class="container py-5">
        <h1>Room Categories</h1>

        <a href="{{ route('room-categories.create') }}" class="btn btn-primary mb-3">Add Category</a>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ $category->price }}</td>
                        <td>
                            <a href="{{ route('room-categories.edit', $category) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('room-categories.destroy', $category) }}" method="POST" class="d-inline">
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
