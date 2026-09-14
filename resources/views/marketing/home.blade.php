{{--
    BL-009 - Home page. Replaces the foundation's shell placeholder at '/'.
    Renders live RoomCategory pricing per CQ-008 (legacy hardcoded these as
    static $99/$149/$199 text). Functional-only: no UI fidelity work has
    been done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', config('app.name'))

@section('content')
    @include('partials.marketing-nav')

    <main class="container">
        <h1>{{ config('app.name') }}</h1>

        <h2>Room Categories</h2>
        <div class="row">
            @foreach ($categories as $category)
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">{{ $category->name }}</h5>
                            <p class="card-text">${{ $category->price }} / night</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </main>
@endsection
