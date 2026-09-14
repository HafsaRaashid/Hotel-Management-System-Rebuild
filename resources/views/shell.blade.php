{{--
    Shell placeholder view (frontend-shell pillar). Proves the layout and
    render pipeline work end to end. Carries no capability content - no
    MOD-006 marketing copy, no booking form, no admin screen. The root
    route above is expected to be replaced by the Public Marketing Site
    module's own backlog item once it is built.
--}}
@extends('layouts.app')

@section('title', config('app.name') . ' - scaffold')

@section('content')
    <main class="container py-5">
        <h1>{{ config('app.name') }}</h1>
        <p class="text-muted">
            Application shell scaffold. No capability has been built yet -
            see .specclaw/analysis/rebuild-backlog.md for the backlog this
            foundation carries.
        </p>
    </main>
@endsection
