{{--
    BL-009 - Room Details page. Renders live RoomCategory pricing per CQ-008,
    same as home.blade.php. Functional-only: no UI fidelity work has been
    done (/specclaw:bf-ui has not run).
--}}
@extends('layouts.app')

@section('title', 'Rooms - ' . config('app.name'))

@section('content')
    @include('partials.marketing-nav')

    <main class="container">
        <h1>Room Details</h1>

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
