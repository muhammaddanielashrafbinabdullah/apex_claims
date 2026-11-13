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

<div x-data="adminReview(@js($payload), '{{ url('/admin/claims') }}')"
     @keydown.escape.window="close()" class="mx-auto max-w-6xl space-y-6">

    <h1 class="text-white text-4xl font-extrabold tracking-tight">DASHBOARD</h1>

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

    {{-- Count cards (follow the Month / Year filters) --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border-2 border-slate-800 bg-white p-4"><p class="text-xs font-semibold uppercase text-slate-500">Total submitted</p><p class="text-3xl font-extrabold">{{ $stats['submitted'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs font-semibold uppercase text-amber-700">Pending</p><p class="text-3xl font-bold">{{ $stats['pending'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs font-semibold uppercase text-green-700">Approved</p><p class="text-3xl font-bold">{{ $stats['approved'] }}</p></div>
        <div class="rounded-lg border bg-white p-4"><p class="text-xs font-semibold uppercase text-red-700">Rejected</p><p class="text-3xl font-bold">{{ $stats['rejected'] }}</p></div>
    </div>

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
                        <td class="px-4 py-3 text-left font-semibold">RM <span x-text="fmt(claimTotal({{ $claim->id }}))">{{ number_format($claim->total_amount, 2) }}</span></td>
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

    <div class="rounded-lg bg-white px-3 py-2 empty:hidden">{{ $claims->links() }}</div>

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
                <button type="button" @click="close()" class="rounded-full p-1.5 hover:bg-slate-200" aria-label="Close">
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
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

                    <p x-show="isPending()" class="text-sm text-slate-500">Click a line to review it.</p>
                    
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
                                        :class="{
                                            'cursor-pointer hover:bg-sky-50': canReview('{{ $k }}') && !isOut('{{ $k }}'),
                                            'cursor-pointer bg-slate-100 text-slate-400': isOut('{{ $k }}') && canReview('{{ $k }}'),
                                            'bg-slate-100 text-slate-400': isOut('{{ $k }}') && !canReview('{{ $k }}'),
                                            'bg-sky-100': activeKey === '{{ $k }}'
                                        }"
                                        @click="toggle('{{ $k }}')">
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
                                    {{-- Small panel that opens when the admin clicks this line --}}
                                    <tr x-show="activeKey === '{{ $k }}'" x-cloak class="border-b border-slate-300 bg-sky-50">
                                        <td colspan="7" class="px-3 py-3" @click.stop>
                                            <p x-show="err && activeKey === '{{ $k }}'" x-cloak class="mb-2 text-right text-xs font-semibold text-red-700" x-text="err"></p>
                                            <div class="flex flex-wrap items-center justify-end gap-3">
                                                <span x-show="isOut('{{ $k }}') && reasonOf('{{ $k }}')" class="mr-auto text-xs text-slate-600">
                                                    Declined – <span x-text="reasonOf('{{ $k }}')"></span>
                                                </span>
                                                <input x-show="!isOut('{{ $k }}')" type="text" x-model="reason" maxlength="500"
                                                       placeholder="Reason for declining, e.g. Receipt not provided" class="w-96 rounded border-slate-300 text-sm">
                                                <button type="button" x-show="!isOut('{{ $k }}')" @click="send('{{ $k }}', 'disallow')" :disabled="busy"
                                                        class="rounded-lg bg-red-600 px-4 py-1.5 text-sm font-bold text-white hover:bg-red-700 disabled:opacity-50">Decline</button>
                                                <button type="button" x-show="isOut('{{ $k }}')" @click="send('{{ $k }}', 'restore')" :disabled="busy"
                                                        class="rounded-lg border border-slate-700 px-4 py-1.5 text-sm font-bold hover:bg-slate-800 hover:text-white disabled:opacity-50">Reinstate</button>
                                            </div>
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

            {{-- Admin action footer --}}
            <div class="relative border-t bg-white px-6 py-4" x-show="selectedClaim && selectedClaim.status === 'pending'">
                <div class="flex flex-wrap items-start justify-end gap-3">
                    <form method="POST" :action="selectedClaim ? '{{ url('/admin/claims') }}/' + selectedClaim.id + '/status' : '#'">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="approved">
                        <button :disabled="!hasValid" class="rounded-lg bg-green-600 px-6 py-2.5 font-bold text-white hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-40">Approve</button>
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
        </div>
    </div>
</div>
@endsection
