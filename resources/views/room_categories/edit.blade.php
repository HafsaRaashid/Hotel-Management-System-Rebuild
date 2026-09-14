@extends('layouts.app')

@section('title', 'Edit Room Category')

@section('content')
    <main class="container py-5">
        <h1>Edit Room Category</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('room-categories.update', $category) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $category->name) }}">
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" min="0" class="form-control" id="price" name="price" value="{{ old('price', $category->price) }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('room-categories.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
@endsection
