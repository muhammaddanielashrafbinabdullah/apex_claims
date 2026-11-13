<!DOCTYPE html>
<html lang="en" class="overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Apex Claims')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-[#263c76] font-sans text-slate-800 antialiased">

<div class="fixed inset-0 flex flex-col">
        <div class="flex min-h-0 flex-1">
            {{-- Sidebar: floating white panel with a thin blue margin and rounded corners --}}
            <div class="flex shrink-0 py-3 pl-3">
            <aside class="flex w-60 shrink-0 flex-col overflow-y-auto overscroll-contain rounded-3xl bg-white text-slate-700 shadow-lg">
                <div class="px-5 py-6">
                    <img src="{{ asset('images/logo.png') }}" alt="Apex Claims Portal" class="mx-auto h-auto w-full">
                </div>

                <nav class="flex-1 space-y-1 px-3">
                    @php
                        $link = fn (bool $active) => 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition '
                            .($active ? 'bg-[#263c76] text-white shadow' : 'text-slate-600 hover:bg-slate-100');
                    @endphp

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="{{ $link(request()->routeIs('admin.dashboard')) }}">
                            <x-heroicon-o-squares-2x2 class="h-5 w-5" /> Dashboard
                        </a>
                    @else
                        <a href="{{ route('claims.create') }}" class="{{ $link(request()->routeIs('claims.create')) }}">
                            <x-heroicon-o-plus-circle class="h-5 w-5" /> Submit New Claim
                        </a>
                        <a href="{{ route('claims.history') }}" class="{{ $link(request()->routeIs('claims.history')) }}">
                            <x-heroicon-o-clock class="h-5 w-5" /> History
                        </a>
                    @endif
                </nav>

                <div class="p-4">
                    <p class="mb-2 truncate px-1 text-xs text-slate-500">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-300 px-3 py-2.5 text-sm font-semibold uppercase tracking-wide text-slate-700 hover:bg-slate-100">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" /> Log Out
                        </button>
                    </form>
                </div>
            </aside>
            </div>

            {{-- Main: second floating panel, same rounded corners and blue margin as the sidebar --}}
            <div class="flex min-w-0 flex-1 p-3">
            <main class="min-w-0 flex-1 overflow-y-auto overscroll-contain rounded-3xl bg-[#edf1f8] px-8 pb-4 pt-8 shadow-lg">
                @if (session('status'))
                    <div class="mb-4 rounded-md border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
                @endif
                @if ($errors->has('status'))
                    <div class="mb-4 rounded-md border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first('status') }}</div>
                @endif

                @yield('content')
            </main>
            </div>
        </div>
</div>
</body>
</html>
