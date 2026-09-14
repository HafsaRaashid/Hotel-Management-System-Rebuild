{{--
    BL-009 - shared nav for the four public marketing pages, matching the
    legacy homepage/Header.php's Home/Room/Services/Foods/Book Now structure.
    "Book Now" now points at the real booking form - BL-015 (MOD-004) landed
    in change 005-booking-stay-lifecycle. See that change's spec.md FR-23.
--}}
<nav class="navbar navbar-expand navbar-light bg-light mb-4">
    <div class="container">
        <ul class="navbar-nav">
            <li class="nav-item"><a class="nav-link" href="{{ route('marketing.home') }}">Home</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('marketing.room') }}">Room</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('marketing.services') }}">Services</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('marketing.food') }}">Foods</a></li>
            <li class="nav-item"><a class="nav-link" href="{{ route('booking.create') }}">Book Now</a></li>
        </ul>
    </div>
</nav>
