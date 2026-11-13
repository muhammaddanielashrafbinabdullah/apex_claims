@extends('layouts.app')
@section('title', 'History – Apex')

@section('content')
@php
    $tz = config('claim_rates.timezone');
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $left = ['full_name' => 'Full Name', 'designation' => 'Designation', 'department' => 'Department', 'staff_no' => 'Staff No', 'bank_account' => 'Bank & Account No'];
    $right = ['vehicle_reg_no' => 'Vehicle registration no', 'vehicle_type' => 'Type', 'vehicle_model' => 'Vehicle Model', 'vehicle_capacity' => 'Capacity', 'vehicle_ownership' => 'Ownership'];
    $box = 'min-h-[2rem] border border-slate-400 bg-slate-100 px-2 py-1 text-slate-800';
    $badge = [
        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
        'approved' => 'bg-green-100 text-green-800 border-green-300',
        'rejected' => 'bg-red-100 text-red-800 border-red-300',
    ];
@endphp

<div x-data="claimView(@js($payload))" @keydown.escape.window="close()" class="mx-auto max-w-6xl space-y-6">
    <h1 class="text-white text-4xl font-extrabold tracking-tight">HISTORY</h1>

    {{-- Action bar --}}
    <form method="GET" action="{{ route('claims.history') }}" class="flex flex-wrap items-center gap-3">
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
                    <th class="w-40 px-4 py-3 text-left">Claimed Amount</th>
                    <th class="w-40 px-4 py-3 text-left">Approved Amount</th>
                    <th class="w-36 px-4 py-3">Status</th>
                </tr>
            </thead>
            @forelse ($claims as $claim)
                <tbody class="border-b last:border-0">
                    <tr class="cursor-pointer align-top hover:bg-sky-50" @click="open({{ $claim->id }})" title="Click to view this claim">
                        <td class="px-4 py-3">{{ $claim->submitted_at->timezone($tz)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 font-mono">{{ $claim->claim_number }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $claim->staff_categories }}</td>
                        <td class="px-4 py-3 text-left font-semibold">RM {{ number_format($claim->claimed_total, 2) }}</td>
                        <td class="px-4 py-3 text-left font-semibold">
                            @if ($claim->status === 'approved') RM {{ number_format($claim->total_amount, 2) }} @else <span class="font-normal text-slate-400">–</span> @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full border px-3 py-0.5 text-xs font-semibold {{ $badge[$claim->status] }}">{{ ucfirst($claim->status) }}</span>
                        </td>
                    </tr>
                </tbody>
            @empty
                <tbody><tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No claims found.</td></tr></tbody>
            @endforelse
        </table>
    </div>

    <div class="flex flex-wrap items-center justify-end gap-4">
        {{-- Pagination --}}
        <form method="GET" action="{{ route('claims.history') }}" class="flex items-center gap-2 rounded-lg bg-white px-3 py-2 text-sm">
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
    {{-- ============ Review pop-up: same layout as the staff form ============ --}}
    <div x-show="openModal" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="close()">
        <div class="relative flex max-h-[92vh] w-full max-w-7xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl" @click.stop>


            {{-- Header --}}
            <div class="relative flex items-center justify-between border-b bg-white/80 px-6 py-4">
                <div class="flex flex-wrap items-baseline gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight" x-text="selectedClaim ? 'CLAIM OF ' + selectedClaim.period : 'CLAIM'"></h2>
                    <span class="font-mono text-sm text-slate-500" x-text="selectedClaim ? '#' + selectedClaim.claim_number : ''"></span>
                </div>
                <div class="flex items-center gap-3">
                    {{-- Status stamp: Approved / Rejected only (Pending stays blank) --}}
                    <img x-show="selectedClaim && selectedClaim.status === 'approved'" x-cloak src="{{ asset('images/stamp-approved.png') }}" alt="Approved" class="-my-3 h-24 w-auto select-none">
                    <img x-show="selectedClaim && selectedClaim.status === 'rejected'" x-cloak src="{{ asset('images/stamp-rejected.png') }}" alt="Rejected" class="-my-3 h-24 w-auto select-none">
                    <button type="button" @click="close()" class="rounded-full p-1.5 hover:bg-slate-200" aria-label="Close">
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>
            </div>

            <template x-if="selectedClaim">
                <div class="relative flex-1 space-y-6 overflow-y-auto p-6">

                    {{-- Employee info: display boxes, dash when empty --}}
                    <section class="overflow-hidden rounded-lg border-2 border-slate-800">
<div class="bg-slate-800 px-4 py-2 text-left font-bold text-white">Claimant Details</div>
<div class="p-5">
<div class="grid gap-x-8 gap-y-3 md:grid-cols-2">
                            @foreach ([$left, $right] as $col)
                                <div class="space-y-3">
                                    @foreach ($col as $field => $label)
                                        <div>
                                            <span class="mb-1 block text-xs font-semibold">{{ $label }}</span>
                                            <div class="{{ $box }}" x-text="selectedClaim.{{ $field }} || '—'"></div>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
</section>

                    
                    {{-- Claims table: same 7 columns as the form, fits the pop-up (no sideways scroll) --}}
                    <section class="rounded-lg border-2 border-slate-800">
                        <table class="w-full table-fixed border-collapse text-sm">
                            <colgroup>
                                <col class="w-[22%]"><col class="w-[12%]"><col class="w-[15%]"><col class="w-[16%]"><col class="w-[15%]"><col class="w-[4%]"><col class="w-[16%]">
                            </colgroup>
                            <thead><tr><th colspan="7" class="bg-slate-800 px-4 py-2 text-left text-white">Types of Claims allowed</th></tr></thead>
                            <tbody>
                                @foreach ($rows as $row)
                                    @php $isMileage = $row['layout'] === 'mileage'; $k = $row['key']; @endphp
                                    <tr class="border-b border-slate-300 {{ $row['title'] ? 'border-t border-t-slate-400' : '' }}"
                                        :class="isOut('{{ $k }}') && 'bg-slate-100 text-slate-400'">
                                        <td class="px-3 py-2 align-middle">
                                            @if ($row['title']) <span class="block font-semibold">{{ $row['title'] }}</span> @endif
                                            @if ($row['sub']) <span class="block text-xs font-normal text-slate-500">{{ $row['sub'] }}</span> @endif
                                        </td>

                                        @if ($isMileage)
                                            <td class="px-2 py-2">{{ $row['tier'] }}</td>
                                            <td class="px-2 py-2">{{ $row['rate_text'] }}</td>
                                            <td colspan="2" class="px-2 py-2">
                                                <div class="flex items-center justify-center gap-2">
                                                    <div class="{{ $box }} w-28 min-w-0 text-right" :class="isOut('{{ $k }}') && 'line-through'" x-text="qty('{{ $k }}') || '—'"></div>
                                                    <span class="text-slate-600">km</span>
                                                </div>
                                            </td>
                                        @else
                                            <td class="px-2 py-2"><div class="{{ $box }} text-right" :class="isOut('{{ $k }}') && 'line-through'" x-text="qty('{{ $k }}') || '—'"></div></td>
                                            <td class="px-2 py-2 text-slate-600">{{ $row['unit'] }}</td>
                                            <td class="px-2 py-2">{{ $row['rate_text'] }}</td>
                                            <td class="px-2 py-2 text-slate-600">{{ $row['rule'] }}</td>
                                        @endif

                                        <td class="px-2 py-2 text-right font-semibold">RM</td>
                                        <td class="px-2 py-2"><div class="{{ $box }} text-right font-mono" :class="isOut('{{ $k }}') && 'line-through'" x-text="amt('{{ $k }}')"></div></td>
                                    </tr>
                                    {{-- Reason shown under a declined line --}}
                                    <tr x-show="isOut('{{ $k }}')" x-cloak class="border-b border-slate-300 bg-red-50">
                                        <td colspan="7" class="px-3 py-1.5 text-xs text-red-700">
                                            <span class="font-semibold">Not approved</span><span x-show="reasonOf('{{ $k }}')"> – <span x-text="reasonOf('{{ $k }}')"></span></span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-100">
                                    <td colspan="5" class="px-3 py-3 text-right font-extrabold">TOTAL</td>
                                    <td class="px-2 py-3 text-right font-semibold">RM</td>
                                    <td class="px-2 py-3"><div class="min-h-[2rem] border-2 border-slate-800 bg-white px-2 py-1 text-right font-mono font-bold" x-text="fmt(selectedClaim.total_amount)"></div></td>
                                </tr>
                            </tfoot>
                        </table>
                    </section>

                    <section class="overflow-hidden rounded-lg border-2 border-slate-800">
<div class="bg-slate-800 px-4 py-2 text-left font-bold text-white">Receipts & Proof</div>
<div class="p-5">
                        <p x-show="!selectedClaim.attachments.length" class="text-sm text-slate-500">No attachments.</p>
                        <ul class="flex flex-wrap gap-2">
                            <template x-for="file in selectedClaim.attachments" :key="file.id">
                                <li>
                                    <a :href="file.url" target="_blank" rel="noopener"
                                       class="flex items-center gap-2 rounded-full border border-slate-400 bg-slate-100 px-3 py-1 text-sm hover:bg-slate-200">
                                        <span class="rounded px-1 text-[10px] font-bold text-white"
                                              :class="file.type === 'pdf' ? 'bg-red-600' : 'bg-blue-600'" x-text="file.type.toUpperCase()"></span>
                                        <span class="max-w-[16rem] truncate" x-text="file.name"></span>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
</section>

                    <p x-show="selectedClaim.status === 'rejected' && selectedClaim.rejection_reason" class="text-sm text-red-700">
                        Rejection reason: <span x-text="selectedClaim.rejection_reason"></span>
                    </p>
                </div>
            </template>

        </div>
    </div>
</div>
@endsection
