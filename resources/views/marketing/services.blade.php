{{--
    BL-009 - Services page. Static content only - no entity data involved
    (functional-spec.md's capability description states "No form input" and
    names no entity for this page). Functional-only: no UI fidelity work has
    been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Services - ' . config('app.name'))

@section('content')
    @include('partials.marketing-nav')

    <main class="container">
        <h1>Services</h1>
        <p>Information about hotel services.</p>
    </main>
@endsection
