<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')            
</head>
<body class="@if(request()->is('login') || request()->is('register')) body-bg @endif">
    <div id="app">
        <main>
            @yield('content')
        </main>        
        @vite(['resources/js/app.js'])
        @include('partials.scripts')        
        @stack('scripts')
    </div>
</body>
</html>
