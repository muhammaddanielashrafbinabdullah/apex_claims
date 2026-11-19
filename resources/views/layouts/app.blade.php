<!DOCTYPE html>
<html lang="en" class="overflow-hidden">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Apex Claims')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|montserrat:500,600,700,800&display=swap" rel="stylesheet">
    <style>.mont{font-family:'Montserrat','Inter',ui-sans-serif,system-ui,sans-serif}</style>
    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-screen overflow-hidden bg-[#1c2f6a] font-sans text-slate-800 antialiased">
<svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" class="pointer-events-none fixed inset-0 h-full w-full" aria-hidden="true">
<rect width="1440" height="900" fill="#1c2f6a"/>
<g fill="none" stroke="#fff" stroke-width="1" opacity=".07"><circle cx="1440" cy="0" r="220"/><circle cx="1440" cy="0" r="320"/><circle cx="0" cy="900" r="240"/><circle cx="0" cy="900" r="340"/></g>
<path d="M-20 880 C400 800 900 900 1460 820" fill="none" stroke="#d9262c" stroke-width="1.2" opacity=".35"/>
</svg>

<div class="fixed inset-0 flex flex-col">
        <div class="flex min-h-0 flex-1">
            {{-- Sidebar: floating blue panel (same telecom skyline as the login page) --}}
            <div class="flex shrink-0 py-3 pl-3">
            <aside class="relative flex w-60 shrink-0 flex-col overflow-hidden rounded-3xl text-[#263c76] shadow-[0_10px_30px_rgba(8,15,40,.45)]">
<svg viewBox="0 0 240 700" preserveAspectRatio="xMidYMax slice" class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true">
<defs>
 <linearGradient id="sbg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset=".55" stop-color="#f1f5fc"/><stop offset="1" stop-color="#dbe5f5"/></linearGradient>
 <linearGradient id="sfog" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff" stop-opacity="0"/><stop offset="1" stop-color="#ffffff" stop-opacity=".35"/></linearGradient>
 <linearGradient id="trg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#263c76" stop-opacity=".95"/><stop offset="1" stop-color="#263c76" stop-opacity=".35"/></linearGradient><linearGradient id="crv" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#263c76" stop-opacity=".25"/><stop offset=".5" stop-color="#263c76" stop-opacity=".9"/><stop offset="1" stop-color="#263c76" stop-opacity=".35"/></linearGradient><linearGradient id="arcg" x1="0" y1="0" x2="0.4" y2="1"><stop offset="0" stop-color="#263c76" stop-opacity=".25"/><stop offset=".5" stop-color="#263c76" stop-opacity=".95"/><stop offset="1" stop-color="#263c76" stop-opacity=".3"/></linearGradient><filter id="glow" x="-50%" y="-50%" width="200%" height="200%"><feGaussianBlur stdDeviation="2.2" result="b"/><feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge></filter>
<linearGradient id="skf" gradientUnits="userSpaceOnUse" x1="0" y1="585" x2="0" y2="619"><stop offset="0" stop-color="#fff"/><stop offset="1" stop-color="#000"/></linearGradient><mask id="skm" maskUnits="userSpaceOnUse" x="0" y="0" width="240" height="700"><rect width="240" height="700" fill="url(#skf)"/></mask>
</defs>
<rect width="240" height="700" fill="url(#sbg)"/>
<g mask="url(#skm)"><g transform="translate(0 45) scale(1 .82)"><g fill="#8aa1cd" opacity="0.34"><rect x="0" y="594" width="16" height="106"/><rect x="16" y="597" width="20" height="103"/><rect x="36" y="602" width="12" height="98"/><rect x="51" y="608" width="20" height="92"/><rect x="74" y="628" width="12" height="72"/><rect x="88" y="595" width="16" height="105"/><rect x="106" y="652" width="12" height="48"/><rect x="121" y="609" width="12" height="91"/><rect x="133" y="611" width="20" height="89"/><rect x="153" y="660" width="12" height="40"/><rect x="165" y="654" width="16" height="46"/><rect x="183" y="610" width="24" height="90"/><rect x="209" y="635" width="12" height="65"/><rect x="224" y="617" width="20" height="83"/></g>
<g fill="#5b72a8" opacity=".5"><polygon points="163,700 165,640 167,580 168.5,542 175.5,542 177,580 179,640 181,700"/><rect x="171.2" y="518" width="1.6" height="26"/><polygon points="189,700 191,640 193,580 194.5,542 201.5,542 203,580 205,640 207,700"/><rect x="197.2" y="518" width="1.6" height="26"/><rect x="174" y="600" width="22" height="4"/><polygon points="48,700 49.5,600 50,510 51,510 51.5,600 53,700"/><ellipse cx="50.5" cy="532" rx="6" ry="5"/><rect x="49.8" y="482" width="1.4" height="34"/></g>
<rect y="440" width="240" height="260" fill="url(#sfog)"/>
<g fill="#7f97c6" opacity="0.45"><rect x="0" y="661" width="20" height="39"/><rect x="20" y="675" width="24" height="25"/><rect x="44" y="676" width="16" height="24"/><rect x="60" y="653" width="12" height="47"/><rect x="74" y="647" width="16" height="53"/><rect x="90" y="650" width="24" height="50"/><rect x="114" y="656" width="16" height="44"/><rect x="133" y="675" width="24" height="25"/><rect x="159" y="669" width="24" height="31"/><rect x="183" y="645" width="20" height="55"/><rect x="205" y="669" width="12" height="31"/><rect x="217" y="644" width="24" height="56"/></g></g></g>
</svg>
                <div class="relative px-5 pb-2 pt-6">
                    <img src="{{ asset('images/logo-transparent.png') }}" alt="Apex Claims Portal" class="mx-auto h-auto w-full">
                </div>

                <nav class="mont relative flex-1 space-y-2 overflow-y-auto px-4 pt-5">
                    @php
                        $link = fn (bool $active) => 'flex items-center gap-3 rounded-full px-4 py-2.5 text-sm font-semibold tracking-wide transition duration-200 '
                            .($active ? 'bg-[#263c76] text-white shadow-lg shadow-[#263c76]/25' : 'text-[#263c76] hover:bg-[#263c76]/10');
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

                <div class="mont relative p-4">
                    <p class="mb-2 truncate px-1 text-xs font-medium text-slate-500">{{ auth()->user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="flex w-full items-center justify-center gap-2 rounded-full border border-[#263c76]/40 bg-white/60 px-3 py-2.5 text-sm font-bold uppercase tracking-wide text-[#263c76] transition duration-200 hover:bg-[#263c76] hover:text-white">
                            <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" /> Log Out
                        </button>
                    </form>
                </div>
            </aside>
            </div>

            {{-- Main: second floating panel with a soft static pattern --}}
            <div class="flex min-w-0 flex-1 p-3">
            <div class="relative flex min-w-0 flex-1 overflow-hidden rounded-3xl bg-[#edf1f8] shadow-[0_10px_30px_rgba(8,15,40,.45)]">
<svg viewBox="0 0 1200 800" preserveAspectRatio="xMidYMid slice" class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true">
<defs>
 <pattern id="mdots" width="26" height="26" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.2" fill="#263c76" opacity=".10"/></pattern>
 <radialGradient id="mglow" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#263c76" stop-opacity=".10"/><stop offset="1" stop-color="#263c76" stop-opacity="0"/></radialGradient>
 <radialGradient id="mred" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#d9262c" stop-opacity=".07"/><stop offset="1" stop-color="#d9262c" stop-opacity="0"/></radialGradient>
</defs>
<rect width="1200" height="800" fill="url(#mdots)"/>
<circle cx="80" cy="0" r="380" fill="url(#mglow)"/>
<circle cx="1200" cy="800" r="340" fill="url(#mred)"/>
<g fill="none" stroke="#263c76" stroke-width="1" opacity=".08"><circle cx="1200" cy="800" r="180"/><circle cx="1200" cy="800" r="260"/><circle cx="1200" cy="800" r="340"/><circle cx="1200" cy="800" r="420"/></g>
<circle cx="1200" cy="800" r="300" fill="none" stroke="#d9262c" stroke-width="1" stroke-dasharray="3 10" opacity=".22"/>
<path d="M-20 760 C300 650 620 820 900 700 S1150 650 1230 670" fill="none" stroke="#d9262c" stroke-width="1.2" opacity=".28"/>
</svg>
            <main class="relative min-w-0 flex-1 overflow-y-auto overscroll-contain px-8 pb-4 pt-8">
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
</div>
</body>
</html>
