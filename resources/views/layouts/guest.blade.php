<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LUMEN MART') }} — Autentikasi</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f5f7] font-sans text-[#1d1d1f] antialiased">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
            <a href="{{ route('home') }}" class="mb-6">
                <x-application-logo />
            </a>

            <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-[0_8px_32px_rgba(0,0,0,0.06)] ring-1 ring-black/[0.05]">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-xs font-medium text-[#86868b] hover:text-[#1d1d1f]">
                &larr; Kembali ke Katalog LUMEN MART
            </a>
        </div>
    </body>
</html>
