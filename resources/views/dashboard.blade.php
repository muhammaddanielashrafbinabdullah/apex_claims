@extends('layouts.app')
@section('title', 'Dashboard – Apex')

@section('content')
<h1 class="mb-6 text-4xl font-extrabold tracking-tight">DASHBOARD</h1>

<div class="mb-8 grid gap-4 sm:grid-cols-3">
    <div class="rounded-lg border bg-white p-5"><p class="text-sm text-slate-500">Pending claims</p><p class="text-3xl font-bold">{{ $pending }}</p></div>
    <div class="rounded-lg border bg-white p-5"><p class="text-sm text-slate-500">Approved claims</p><p class="text-3xl font-bold">{{ $approved }}</p></div>
    <div class="rounded-lg border bg-white p-5"><p class="text-sm text-slate-500">Claimed this month</p><p class="text-3xl font-bold">RM {{ number_format($monthTotal, 2) }}</p></div>
</div>

<div class="rounded-lg border bg-white">
    <div class="border-b px-5 py-3 font-semibold">Recent claims</div>
    @forelse ($recent as $c)
        <div class="flex justify-between border-b px-5 py-3 text-sm last:border-0">
            <span>#{{ $c->claim_number }} · {{ $c->submitted_at->format('d M Y') }}</span>
            <span>RM {{ number_format($c->total_amount, 2) }} · {{ ucfirst($c->status) }}</span>
        </div>
    @empty
        <p class="px-5 py-6 text-sm text-slate-500">No claims yet. <a class="underline" href="{{ route('claims.create') }}">Submit your first claim</a>.</p>
    @endforelse
</div>
@endsection
