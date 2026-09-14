@extends('layouts.app')

@section('title', 'Add Room')

@section('content')
    <main class="container py-5">
        <h1>Add Room</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('rooms.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="room" class="form-label">Room</label>
                <input type="text" class="form-control" id="room" name="room" value="{{ old('room') }}">
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select class="form-select" id="category_id" name="category_id">
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="0" @selected(old('status', '0') === '0')>Available</option>
                    <option value="1" @selected(old('status') === '1')>Unavailable</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
@endsection
