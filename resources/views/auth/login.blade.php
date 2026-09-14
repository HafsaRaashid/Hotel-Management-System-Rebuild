{{--
    BL-010 - Admin Login. Functional-only: no UI fidelity work has been
    done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Admin Login')

@section('content')
    <main class="container py-5" style="max-width: 400px;">
        <h1>Admin Login</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('login.attempt') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="{{ old('username') }}">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>
    </main>
@endsection
