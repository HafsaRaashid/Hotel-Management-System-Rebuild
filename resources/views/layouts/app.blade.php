<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>

    {{--
        SQ-006: Laravel Blade views with Bootstrap retained, no separate
        frontend build. Bootstrap is loaded from CDN, matching the legacy
        app's own asset strategy (see target-architecture.md's System
        Context - "Third-party CDN asset providers").
    --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    {{--
        theme-plumbing pillar (SQ-013 THEME-ONLY). Mechanism only: the
        token structure exists as CSS custom properties, but no TK- value
        is imported here because .specclaw/ui/design-tokens.json does not
        exist in this repo (the UI workstream has not run). See
        theme.css's own header comment.
    --}}
    <link href="{{ asset('css/theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>
    <div id="app-shell">
        @yield('content')
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"
    ></script>

    @stack('scripts')
</body>
</html>
