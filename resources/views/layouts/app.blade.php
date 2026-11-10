<!DOCTYPE html>
<html lang="en">
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
<body class="bg-slate-300 font-sans text-slate-800 antialiased">

{{-- Browser chrome frame --}}
<div class="mx-auto flex h-screen max-w-[1600px] flex-col p-3">
    <div class="flex flex-1 flex-col overflow-hidden rounded-xl border border-slate-400 bg-white shadow-2xl">

        <div class="flex items-center gap-3 border-b border-slate-300 bg-slate-200 px-4 py-2">
            <div class="flex gap-1.5">
                <span class="h-3 w-3 rounded-full bg-red-400"></span>
                <span class="h-3 w-3 rounded-full bg-amber-400"></span>
                <span class="h-3 w-3 rounded-full bg-green-400"></span>
            </div>
            <div class="flex flex-1 items-center gap-2 rounded-md bg-white px-3 py-1 text-xs text-slate-500">
                <x-heroicon-s-lock-closed class="h-3 w-3" />
                {{ request()->getHost() }}{{ request()->getPathInfo() }}
            </div>
        </div>

        <div class="flex min-h-0 flex-1">
            {{-- Sidebar --}}
            <aside class="flex w-60 shrink-0 flex-col bg-slate-900 text-slate-200">
                <div class="px-6 py-8 text-3xl font-extrabold tracking-[0.3em] text-white">APEX</div>

                <nav class="flex-1 space-y-1 px-3">
                    @php
                        $link = fn (bool $active) => 'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition '
                            .($active ? 'bg-white text-slate-900 shadow' : 'text-slate-300 hover:bg-slate-800');
                    @endphp

                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="{{ $link(request()->routeIs('admin.dashboard')) }}">
                            <x-heroicon-o-squares-2x2 class="h-5 w-5" /> Dashboard
                        </a>
                    @else
                        <a href="{{ route('dashboard') }}" class="{{ $link(request()->routeIs('dashboard')) }}">
                            <x-heroicon-o-home class="h-5 w-5" /> Dashboard
                        </a>
                        <a href="{{ route('claims.create') }}" class="{{ $link(request()->routeIs('claims.create')) }}">
                            <x-heroicon-o-plus-circle class="h-5 w-5" /> Submit New Claim
                        </a>
                        <a href="{{ route('claims.history') }}" class="{{ $link(request()->routeIs('claims.history')) }}">
                            <x-heroicon-o-clock class="h-5 w-5" /> My History
                        </a>
                    @endif
                </nav>

                <div class="p-4">
                    <p class="mb-2 truncate px-1 text-xs text-slate-400">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex w-full items-center justify-center gap-2 rounded-lg border border-slate-600 px-3 py-2.5 text-sm font-semibold uppercase tracking-wide hover:bg-slate-800">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" /> Log Out
                        </button>
                    </form>
                </div>
            </aside>

            {{-- Main --}}
            <main class="min-w-0 flex-1 overflow-y-auto bg-slate-50 p-8">
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
