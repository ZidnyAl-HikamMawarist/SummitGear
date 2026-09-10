<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="SummitGear — Sewa alat outdoor berkualitas: tenda, carrier, sleeping bag & 50+ alat lainnya. Booking online, ambil di toko.">

        <title>{{ $title ?? 'SummitGear — Sewa Alat Outdoor Berkualitas' }}</title>
        
        <!-- Google Fonts: Outfit (modern, clean, non-Inter) -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        @fluxAppearance

        <style>
            .font-outfit { font-family: 'Outfit', system-ui, -apple-system, sans-serif; }
        </style>
    </head>
    <body class="bg-white text-[#0f1729] antialiased font-outfit">
        {{ $slot }}

        @livewireScripts
        @fluxScripts
    </body>
</html>
