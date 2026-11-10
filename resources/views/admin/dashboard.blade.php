@extends('layouts.app')
@section('title', 'Admin Dashboard – Apex')

@section('content')
@php
    $tz = config('claim_rates.timezone');
    $months = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
    $badge = [
        'pending' => 'bg-amber-100 text-amber-800 border-amber-300',
        'approved' => 'bg-green-100 text-green-800 border-green-300',
        'rejected' => 'bg-red-100 text-red-800 border-red-300',
    ];
    $left = ['full_name' => 'Full Name', 'designation' => 'Designation', 'department' => 'Department', 'staff_no' => 'Staff No', 'bank_account' => 'Bank & Account No'];
    $right = ['vehicle_reg_no' => 'Vehicle registration no', 'vehicle_type' => 'Type', 'vehicle_model' => 'Vehicle Model', 'vehicle_capacity' => 'Capacity', 'vehicle_ownership' => 'Ownership'];
    // Display-only box (not an input): looks like the staff form's box but cannot be clicked or edited.
    $box = 'min-h-[2rem] border border-slate-400 bg-slate-100 px-2 py-1 text-slate-800';
@endphp

<div x-data="{
        openModal: false,
        selectedClaim: null,
        rejecting: false,
        claims: @js($payload),
        open(id) { this.selectedClaim = this.claims.find(c => c.id === id); this.rejecting = false; this.openModal = true; },
        close() { this.openModal = false; this.rejecting = false; },
        item(key, field) { const i = this.selectedClaim && this.selectedClaim.items[key]; return i ? i[field] : null; },
        fmt(n) { return n === null || n === undefined ? '' : Number(n).toLocaleString('en-MY', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
        qty(key) { const v = this.item(key, 'unit_input'); return v === null ? '' : Number(v).toLocaleString('en-MY', { maximumFractionDigits: 2 }); },
        amt(key) { const v = this.item(key, 'amount'); return v === null ? '—' : this.fmt(v); },
     }"
     @keydown.escape.window="close()" class="mx-auto max-w-6xl space-y-6">

    <h1 class="text-4xl font-extrabold tracking-tight">DASHBOARD</h1>

    {{-- Action bar --}}
    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-3">
        <select name="month" onchange="this.form.submit()" class="rounded-lg border-slate-300">
            <option value="">Month</option>
            @foreach ($months as $n => $name)
                <option value="{{ $n }}" @selected($month == $n)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="year" onchange="this.form.submit()" class="rounded-lg border-slate-300">
            <option value="">Year</option>
            @foreach ($years as $y)
                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
            @endforeach
        </select>
        <a href="{{ route('admin.claims.export', array_filter(['month' => $month, 'year' => $year])) }}"
           class="ml-auto inline-flex items-center gap-2 rounded-lg bg-green-700 px-4 py-2 text-sm font-bold uppercase tracking-wide text-white hover:bg-green-800">
            <x-heroicon-o-arrow-down-tray class="h-5 w-5" /> Export Excel
        </a>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-lg border bg-white">
        <table class="w-full text-sm">
            <thead class="bg-slate-800 text-left text-white">
                <tr>
                    <th class="px-4 py-3">Staff Name</th>
                    <th class="px-4 py-3">Employee ID</th>
                    <th class="px-4 py-3 text-left">Total Claim Amount</th>
                    <th class="px-4 py-3">Date Submitted</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($claims as $claim)
                    <tr class="border-b last:border-0">
                        <td class="px-4 py-3 font-medium">{{ $claim->full_name }}</td>
                        <td class="px-4 py-3">{{ $claim->staff_no }}</td>
                        <td class="px-4 py-3 text-left font-semibold">RM {{ number_format($claim->total_amount, 2) }}</td>
                        <td class="px-4 py-3">{{ $claim->submitted_at->timezone($tz)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block rounded-full border px-3 py-0.5 text-xs font-semibold {{ $badge[$claim->status] }}">{{ ucfirst($claim->status) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <button type="button" @click="open({{ $claim->id }})"
                                    class="rounded border border-slate-700 px-3 py-1 text-xs font-semibold hover:bg-slate-800 hover:text-white">Review Claim</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-slate-500">No claims for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $claims->links() }}</div>

    {{-- ============ Review pop-up: same layout as the staff form, view only ============ --}}
    <div x-show="openModal" x-cloak x-transition.opacity
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="close()">
        <div class="relative flex max-h-[92vh] w-full max-w-7xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl" @click.stop>

            {{-- Watermark --}}
            <div class="pointer-events-none absolute inset-0 flex select-none items-center justify-center overflow-hidden">
                <span class="-rotate-12 text-8xl font-black uppercase tracking-widest text-slate-900/5">(VIEW ONLY)</span>
            </div>

            {{-- Header --}}
            <div class="relative flex items-center justify-between border-b bg-white/80 px-6 py-4">
                <div class="flex flex-wrap items-baseline gap-3">
                    <h2 class="text-3xl font-extrabold tracking-tight" x-text="selectedClaim ? 'CLAIM OF ' + selectedClaim.period : 'CLAIM'"></h2>
                    <span class="text-sm font-semibold text-slate-500">(VIEW ONLY)</span>
                    <span class="font-mono text-sm text-slate-500" x-text="selectedClaim ? '#' + selectedClaim.claim_number : ''"></span>
                </div>
                <button type="button" @click="close()" class="rounded-full p-1.5 hover:bg-slate-200" aria-label="Close">
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>

            <template x-if="selectedClaim">
                <div class="relative flex-1 space-y-6 overflow-y-auto p-6">

                    {{-- Employee info: display boxes, dash when empty --}}
                    <section class="rounded-lg border-2 border-slate-800 p-5">
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
                                    <tr class="border-b border-slate-300 {{ $row['title'] ? 'border-t border-t-slate-400' : '' }}">
                                        <td class="px-3 py-2 align-middle">
                                            @if ($row['title']) <span class="block font-semibold">{{ $row['title'] }}</span> @endif
                                            @if ($row['sub']) <span class="block text-xs font-normal text-slate-500">{{ $row['sub'] }}</span> @endif
                                        </td>

                                        @if ($isMileage)
                                            <td class="px-2 py-2">{{ $row['tier'] }}</td>
                                            <td class="px-2 py-2">{{ $row['rate_text'] }}</td>
                                            <td colspan="2" class="px-2 py-2">
                                                <div class="flex items-center justify-center gap-2">
                                                    <div class="{{ $box }} w-28 min-w-0 text-right" x-text="qty('{{ $k }}') || '—'"></div>
                                                    <span class="text-slate-600">km</span>
                                                </div>
                                            </td>
                                        @else
                                            <td class="px-2 py-2"><div class="{{ $box }} text-right" x-text="qty('{{ $k }}') || '—'"></div></td>
                                            <td class="px-2 py-2 text-slate-600">{{ $row['unit'] }}</td>
                                            <td class="px-2 py-2">{{ $row['rate_text'] }}</td>
                                            <td class="px-2 py-2 text-slate-600">{{ $row['rule'] }}</td>
                                        @endif

                                        <td class="px-2 py-2 text-right font-semibold">RM</td>
                                        <td class="px-2 py-2"><div class="{{ $box }} text-right font-mono" x-text="amt('{{ $k }}')"></div></td>
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

                    {{-- Attachments (open only: no upload, no remove) --}}
                    <section class="rounded-lg border-2 border-slate-800 p-5">
                        <h3 class="mb-3 font-bold">Attach Receipt or Proof</h3>
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
                    </section>

                    <p x-show="selectedClaim.status === 'rejected' && selectedClaim.rejection_reason" class="text-sm text-red-700">
                        Rejection reason: <span x-text="selectedClaim.rejection_reason"></span>
                    </p>
                </div>
            </template>

            {{-- Admin action footer --}}
            <div class="relative border-t bg-white px-6 py-4" x-show="selectedClaim && selectedClaim.status === 'pending'">
                <div class="flex flex-wrap items-start justify-end gap-3">
                    <form method="POST" :action="selectedClaim ? '{{ url('/admin/claims') }}/' + selectedClaim.id + '/status' : '#'">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button class="rounded-lg bg-green-600 px-6 py-2.5 font-bold text-white hover:bg-green-700">Approve</button>
                    </form>

                    <form method="POST" :action="selectedClaim ? '{{ url('/admin/claims') }}/' + selectedClaim.id + '/status' : '#'" class="flex flex-col items-end gap-2">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="rejected">
                        <textarea x-show="rejecting" x-cloak name="rejection_reason" rows="2" maxlength="1000"
                                  placeholder="Reason for rejection (optional)" class="w-72 rounded border-slate-300 text-sm"></textarea>
                        <button type="button" x-show="!rejecting" @click="rejecting = true"
                                class="rounded-lg bg-red-600 px-6 py-2.5 font-bold text-white hover:bg-red-700">Reject</button>
                        <button x-show="rejecting" x-cloak class="rounded-lg bg-red-600 px-6 py-2.5 font-bold text-white hover:bg-red-700">Confirm Reject</button>
                    </form>
                </div>
            </div>
            <div class="relative border-t bg-slate-50 px-6 py-3 text-right text-sm text-slate-500"
                 x-show="selectedClaim && selectedClaim.status !== 'pending'"
                 x-text="selectedClaim ? 'This claim has been ' + selectedClaim.status + '.' : ''"></div>
        </div>
    </div>
</div>
@endsection
