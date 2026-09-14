<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-flux-appearance="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="SummitGear — Sewa alat outdoor berkualitas: tenda, carrier, sleeping bag & 50+ alat lainnya. Booking online, ambil di toko.">

        <title>{{ $title ?? 'SummitGear — Sewa Alat Outdoor Berkualitas' }}</title>
        
        <!-- Google Fonts: Plus Jakarta Sans & Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400;1,600;1,700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
        <script>
            try {
                localStorage.setItem('flux.appearance', 'light');
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } catch (e) {}
        </script>
        @fluxAppearance
        <script>
            try {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
                if (window.Flux) {
                    window.Flux.applyAppearance('light');
                }
            } catch (e) {}
        </script>
    </head>
    <body class="bg-[#F5F6FA] text-slate-900 antialiased font-sans min-h-screen">
        {{ $slot }}

        @livewireScripts
        @fluxScripts
    </body>
</html>
