<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('partials.head')            
</head>
<body class="@if(request()->is('login')) body-bg @endif">
    <div id="app">        
        
        <main>
            @include('partials.sidebar')
            <div class="content bg-blue">  
                <div class="bg-light rounded-5 p-3 content-min-height">
                    @include('partials.inner_header') 
                    <div class="container inner-content py-4">
                        @yield('content')
                    </div>
                    @include('partials.footer')
                </div>                          
            </div>
        </main>
        @vite(['resources/js/app.js'])
        @include('partials.scripts')
        @include('partials.notification')
        
        @stack('scripts')

        @if($authUser->can('report.enable_idle_report'))
            <x-idle-modal />
        @endif
    </div>
</body>
</html>
