{{--
    BL-007 - Customer create. No `customer_id` field: the server generates it
    (DR-004) - it is never a user-editable input, even on create.
--}}
@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
    <main class="container py-5">
        <h1>Add Customer</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('customers.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}">
            </div>

            <div class="mb-3">
                <label for="mail" class="form-label">Mail</label>
                <input type="email" class="form-control" id="mail" name="mail" value="{{ old('mail') }}">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" inputmode="numeric" pattern="[0-9]{7,15}" class="form-control" id="phone" name="phone" value="{{ old('phone') }}">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ old('address') }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
@endsection
