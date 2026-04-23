<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name') }}</title>
    
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-light">
    {{-- Loading Screen Component --}}
    @include('components.loading-screen')

    <div class="container">
        @yield('content')
    </div>
    
    @vite(['resources/js/modules/loading-screen.js'])

    @stack('scripts')
</body>
</html>