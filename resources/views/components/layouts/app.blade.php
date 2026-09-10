<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-flux-appearance="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? 'SummitGear POS' }}</title>
        
        <!-- Google Fonts: Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-[#F5F6FA] text-slate-900 antialiased font-sans">
        {{ $slot }}
        
        @auth
            @if(auth()->user()->role === 'kasir')
                <livewire:components.screen-lock />
            @endif
            <livewire:components.pin-approval />
            <x-logout-modal />
        @endauth

        @livewireScripts
        @fluxScripts
    </body>
</html>
