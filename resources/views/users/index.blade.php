{{--
    BL-012 - User List. Functional-only: no UI fidelity work has been
    done (/specclaw:bf-ui has not run - see .specclaw/changes/009-user-account-management/spec.md NFR-2).
--}}
@extends('layouts.app')

@section('title', 'Users')

@section('content')
    @include('partials.admin-nav')

    <main class="container py-5">
        <h1>Users</h1>

        @if (session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        <a href="{{ route('users.create') }}" class="btn btn-primary mb-3">Add User</a>

        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Type</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->type === \App\Models\User::TYPE_ADMIN ? 'Admin' : 'Staff' }}</td>
                        <td>
                            <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline">
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
