{{--
    BL-009 - Food & Drinks page. Static content only - no entity data
    involved. Functional-only: no UI fidelity work has been done
    (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Food & Drinks - ' . config('app.name'))

@section('content')
    @include('partials.marketing-nav')

    <main class="container">
        <h1>Food & Drinks</h1>
        <p>Information about hotel dining options.</p>
    </main>
@endsection
