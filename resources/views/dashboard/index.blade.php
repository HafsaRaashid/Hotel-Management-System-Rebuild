{{--
    BL-024 - Admin Dashboard. Functional-only: no UI fidelity work has been
    done (/specclaw:bf-ui has not run - see .specclaw/changes/008-dashboard-reporting/spec.md NFR-2).
--}}
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @include('partials.admin-nav')

    <main class="container py-5">
        <h1>Dashboard</h1>

        <div class="row row-cols-1 row-cols-md-3 g-3">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Total Bookings</h6>
                        <p class="card-text fs-4">{{ $totalBookings }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Checked In</h6>
                        <p class="card-text fs-4">{{ $checkedIn }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Checked Out</h6>
                        <p class="card-text fs-4">{{ $checkedOut }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Total Payment</h6>
                        <p class="card-text fs-4">{{ $totalPayment }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Available Rooms</h6>
                        <p class="card-text fs-4">{{ $availableRooms }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Total Rooms</h6>
                        <p class="card-text fs-4">{{ $totalRooms }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Total Room Categories</h6>
                        <p class="card-text fs-4">{{ $totalRoomCategories }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Total Customers</h6>
                        <p class="card-text fs-4">{{ $totalCustomers }}</p>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h6 class="card-subtitle text-muted">Total Users</h6>
                        <p class="card-text fs-4">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
