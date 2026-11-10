@extends('layouts.app')
@section('title', 'My History – Apex')

@section('content')
@php
    $tz = config('claim_rates.timezone');
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $badge = [
        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
        'approved' => 'bg-green-100 text-green-800 border-green-300',
        'rejected' => 'bg-red-100 text-red-800 border-red-300',
    ];
@endphp

<div class="mx-auto max-w-6xl space-y-6">
    <h1 class="text-4xl font-extrabold tracking-tight">MY HISTORY</h1>

    {{-- Action bar --}}
    <form method="GET" action="{{ route('claims.history') }}" class="flex flex-wrap items-center gap-3">
        <div class="relative min-w-[16rem] flex-1">
            <x-heroicon-o-magnifying-glass class="pointer-events-none absolute left-3 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400" />
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search claim ID, category or status"
                   class="w-full rounded-lg border-slate-300 pl-10">
        </div>
        <select name="month" onchange="this.form.submit()" class="rounded-lg border-slate-300">
            <option value="">Month</option>
            @foreach ($months as $n => $name)
                <option value="{{ $n }}" @selected(request('month') == $n)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="year" onchange="this.form.submit()" class="rounded-lg border-slate-300">
            <option value="">Year</option>
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected(request('year') == $y)>{{ $y }}</option>
            @endforeach
        </select>
    </form>

    {{-- Table (5 columns, view only) --}}
    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="w-full table-fixed text-sm">
            <thead class="bg-slate-800 text-left text-white">
                <tr>
                    <th class="w-32 px-4 py-3">Date</th>
                    <th class="w-28 px-4 py-3">Claim ID</th>
                    <th class="px-4 py-3">Categories</th>
                    <th class="w-40 px-4 py-3 text-right">Total Amount</th>
                    <th class="w-32 px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($claims as $claim)
                    <tr class="border-b align-top last:border-0">
                        <td class="px-4 py-3">{{ $claim->submitted_at->timezone($tz)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-mono">{{ $claim->claim_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $claim->categories_list }}</td>
                        <td class="px-4 py-3 text-right font-semibold">RM {{ number_format($claim->total_amount, 2) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full border px-3 py-0.5 text-xs font-semibold {{ $badge[$claim->status] }}">{{ ucfirst($claim->status) }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">No claims found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-4">
        {{-- Monthly total (all claims) --}}
        <div class="rounded-lg border-2 border-slate-800 bg-amber-50 px-5 py-3">
            <p class="text-xs font-semibold uppercase text-slate-500">Total claimed – {{ $monthLabel }}</p>
            <p class="text-2xl font-extrabold">RM {{ number_format($monthTotal, 2) }}</p>
        </div>

        {{-- Pagination --}}
        <form method="GET" action="{{ route('claims.history') }}" class="flex items-center gap-2 text-sm">
            @foreach (request()->except('page') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endforeach

            @if ($claims->onFirstPage())
                <span class="rounded border p-1.5 text-slate-300"><x-heroicon-s-chevron-left class="h-4 w-4" /></span>
            @else
                <a href="{{ $claims->previousPageUrl() }}" class="rounded border p-1.5 hover:bg-slate-200"><x-heroicon-s-chevron-left class="h-4 w-4" /></a>
            @endif

            <input type="number" name="page" min="1" max="{{ $claims->lastPage() }}" value="{{ $claims->currentPage() }}"
                   class="w-16 rounded border-slate-300 py-1 text-center">
            <span class="text-slate-500">… of {{ $claims->lastPage() }}</span>

            @if ($claims->hasMorePages())
                <a href="{{ $claims->nextPageUrl() }}" class="rounded border p-1.5 hover:bg-slate-200"><x-heroicon-s-chevron-right class="h-4 w-4" /></a>
            @else
                <span class="rounded border p-1.5 text-slate-300"><x-heroicon-s-chevron-right class="h-4 w-4" /></span>
            @endif
        </form>
    </div>
</div>
@endsection
