{{--
    BL-013 - User create.
--}}
@extends('layouts.app')

@section('title', 'Add User')

@section('content')
    <main class="container py-5">
        <h1>Add User</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <div class="mb-3">
                <label for="type" class="form-label">User Type</label>
                <select class="form-select" id="type" name="type">
                    <option value="1" {{ old('type') == 1 ? 'selected' : '' }}>Admin</option>
                    <option value="2" {{ old('type', 2) == 2 ? 'selected' : '' }}>Staff</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
@endsection
