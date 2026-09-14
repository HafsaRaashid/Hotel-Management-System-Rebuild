{{--
    BL-011 - shared nav for every admin screen, matching the legacy's
    admin/sidebar.php's role. Links only to screens that currently exist -
    "Users" (MOD-002) is intentionally absent, not hidden: there is nothing
    to hide yet. DR-012's role-visibility rule is not implemented here for
    the same reason - see .specclaw/changes/007-admin-authentication/spec.md.
--}}
<nav class="navbar navbar-expand navbar-dark bg-dark mb-4">
    <div class="container">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('customers.index') }}">Customers</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('rooms.index') }}">Rooms</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('room-categories.index') }}">Room Categories</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('bookings.pending') }}">Pending Bookings</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('walk-in.available') }}">Walk-In</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('stays.index') }}">Stays</a></li>
        </ul>
        <span class="navbar-text text-light me-3">{{ auth()->user()->name }}</span>
        <form action="{{ route('logout') }}" method="POST" class="d-inline">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light">Logout</button>
        </form>
    </div>
</nav>
