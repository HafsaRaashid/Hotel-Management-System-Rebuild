{{--
    BL-007 - Customer edit. `customer_id` is displayed read-only (never a
    submitted input) - DR-004 means it is generated once and never
    user-editable, on create or edit.
--}}
@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
    <main class="container py-5">
        <h1>Edit Customer</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3">
            <label class="form-label">Customer Id</label>
            <input type="text" class="form-control" value="{{ $customer->customer_id }}" disabled>
        </div>

        <form action="{{ route('customers.update', $customer) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $customer->name) }}">
            </div>

            <div class="mb-3">
                <label for="mail" class="form-label">Mail</label>
                <input type="email" class="form-control" id="mail" name="mail" value="{{ old('mail', $customer->mail) }}">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" inputmode="numeric" pattern="[0-9]{7,15}" class="form-control" id="phone" name="phone" value="{{ old('phone', $customer->phone) }}">
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $customer->address) }}">
            </div>

            <button type="submit" class="btn btn-primary">Save</button>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </main>
@endsection
