{{--
    BL-006 - Customer List. Functional-only: no UI fidelity work has been
    done (/specclaw:bf-ui has not run - see .specclaw/changes/002-customer-management/spec.md NFR-3).
--}}
@extends('layouts.app')

@section('title', 'Customers')

@section('content')
    <main class="container py-5">
        <h1>Customers</h1>

        @if (session('error'))
            <div class="alert alert-warning">{{ session('error') }}</div>
        @endif

        <a href="{{ route('customers.create') }}" class="btn btn-primary mb-3">Add Customer</a>

        <table class="table">
            <thead>
                <tr>
                    <th>Customer Id</th>
                    <th>Name</th>
                    <th>Mail</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Charges</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($customers as $customer)
                    <tr>
                        <td>{{ $customer->customer_id }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->mail }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->address }}</td>
                        <td>{{ $customer->charges }}</td>
                        <td>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-secondary">Edit</a>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline">
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
