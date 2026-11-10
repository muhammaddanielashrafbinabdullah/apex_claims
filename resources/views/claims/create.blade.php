@extends('layouts.app')
@section('title', 'Submit New Claim – Apex')

@push('head')
<script>window.CLAIM_RATES = @json(config('claim_rates'));</script>
@endpush

@section('content')
<form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data"
      x-data="claimCalculator(@js(old('items', [])))" @submit="validate($event)" class="mx-auto max-w-6xl space-y-6">
    @csrf

    <h1 class="text-left text-5xl font-extrabold tracking-tight">CLAIM OF {{ $period }}</h1>
    <p class="-mt-3 text-sm text-slate-500"><span class="font-bold text-red-600">*</span> Required</p>

    @if ($errors->any())
        <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Employee info (all boxes start empty) --}}
    <section class="rounded-lg border-2 border-slate-800 bg-white p-6">
        @php
            $left = [
                'full_name' => 'Full Name',
                'designation' => 'Designation',
                'department' => 'Department',
                'staff_no' => 'Staff No',
                'bank_account' => 'Bank & Account No',
            ];
            $right = [
                'vehicle_reg_no' => 'Vehicle registration no',
                'vehicle_type' => 'Type',
                'vehicle_model' => 'Vehicle Model',
                'vehicle_capacity' => 'Capacity',
                'vehicle_ownership' => 'Ownership',
            ];
        @endphp
        <div class="grid gap-x-10 gap-y-4 md:grid-cols-2">
            <div class="space-y-4">
                @foreach ($left as $name => $label)
                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold">{{ $label }} <span class="font-bold text-red-600">*</span></span>
                        <input type="text" name="{{ $name }}" value="{{ old($name) }}" autocomplete="off" required
                               class="w-full rounded-none border-slate-500">
                    </label>
                @endforeach
            </div>
            <div class="space-y-4">
                @foreach ($right as $name => $label)
                    <label class="block">
                        <span class="mb-1 block text-sm font-semibold">{{ $label }}</span>
                        <input type="text" name="{{ $name }}" value="{{ old($name) }}" autocomplete="off"
                               class="w-full rounded-none border-slate-500">
                    </label>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Claims table: 7 columns --}}
    <section class="overflow-x-auto rounded-lg border-2 border-slate-800 bg-white">
        <table class="w-full min-w-[60rem] table-fixed border-collapse text-sm">
            <colgroup>
                <col class="w-60"><col class="w-36"><col class="w-40"><col class="w-44"><col class="w-40"><col class="w-12"><col class="w-32">
            </colgroup>
            <thead>
                <tr><th colspan="7" class="bg-slate-800 px-4 py-2 text-left text-white">Types of Claims allowed <span class="font-bold text-red-400">*</span> <span class="ml-2 text-xs font-normal text-slate-300">(fill in at least one)</span></th></tr>
            </thead>
            <tbody>
                @foreach (config('claim_rates.rows') as $row)
                    @php $isMileage = $row['layout'] === 'mileage'; @endphp
                    <tr class="border-b border-slate-300 {{ $row['title'] ? 'border-t border-t-slate-400' : '' }}">
                        {{-- 1 Description --}}
                        <td class="px-3 py-2 align-middle">
                            @if ($row['title']) <span class="block font-semibold">{{ $row['title'] }}</span> @endif
                            @if ($row['sub']) <span class="block text-xs font-normal text-slate-500">{{ $row['sub'] }}</span> @endif
                        </td>

                        @if ($isMileage)
                            {{-- 2 Tier name, 3 Rate, 4 Input + km, 5 blank --}}
                            <td class="px-2 py-2">{{ $row['tier'] }}</td>
                            <td class="px-2 py-2">{{ $row['rate_text'] }}</td>
                            <td class="px-2 py-2">
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.01" min="0" max="{{ $row['max'] }}"
                                           name="items[{{ $row['key'] }}]" x-model.number="form.{{ $row['key'] }}" @wheel="$el.blur()"
                                           class="w-full min-w-0 rounded-none border-slate-500 py-1 text-right">
                                    <span class="text-slate-600">km</span>
                                </div>
                            </td>
                            <td class="px-2 py-2"></td>
                        @else
                            {{-- 2 Input, 3 Unit, 4 Rate, 5 Rule --}}
                            <td class="px-2 py-2">
                                <input type="number" step="0.01" min="0" max="{{ $row['max'] }}"
                                       name="items[{{ $row['key'] }}]" x-model.number="form.{{ $row['key'] }}" @wheel="$el.blur()"
                                       class="w-full rounded-none border-slate-500 py-1 text-right">
                            </td>
                            <td class="px-2 py-2 text-slate-600">{{ $row['unit'] }}</td>
                            <td class="px-2 py-2">{{ $row['rate_text'] }}</td>
                            <td class="px-2 py-2 text-slate-600">{{ $row['rule'] }}</td>
                        @endif

                        {{-- 6 Currency --}}
                        <td class="px-2 py-2 text-right font-semibold">RM</td>
                        {{-- 7 Result --}}
                        <td class="px-2 py-2">
                            <div class="border border-slate-500 bg-slate-100 px-2 py-1 text-right font-mono"
                                 x-text="fmt(amounts.{{ $row['key'] }})">0.00</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="bg-slate-100">
                    <td colspan="5" class="px-3 py-3 text-right text-base font-extrabold">TOTAL</td>
                    <td class="px-2 py-3 text-right font-semibold">RM</td>
                    <td class="px-2 py-3">
                        <div class="border-2 border-slate-800 bg-white px-2 py-1 text-right font-mono font-bold" x-text="fmt(total)">0.00</div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </section>

    {{-- Attachments --}}
    <section class="rounded-lg border-2 border-slate-800 bg-white p-6">
        <h2 class="mb-3 text-lg font-bold">Attach Receipt or Proof <span class="text-sm font-normal text-slate-500">(optional)</span></h2>

        <div class="flex items-center gap-3">
            <label class="cursor-pointer rounded border border-slate-600 bg-slate-100 px-4 py-2 text-sm font-semibold hover:bg-slate-200">
                Choose File
                <input type="file" name="attachments[]" x-ref="fileInput" multiple accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg"
                       class="sr-only" @change="addFiles($event)">
            </label>
            <span class="text-sm text-slate-500" x-text="fileStatus">No file chosen</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">PDF or JPG, up to 10 MB each. Attach as many files as needed.</p>

        <p x-show="fileError" x-cloak x-text="fileError" class="mt-2 text-sm text-red-600"></p>

        <ul class="mt-4 flex flex-wrap gap-2">
            <template x-for="(file, i) in files" :key="file.name + file.size">
                <li class="flex items-center gap-2 rounded-full border border-slate-400 bg-slate-100 py-1 pl-3 pr-1 text-sm">
                    <span x-show="isPdf(file)" class="rounded bg-red-600 px-1 text-[10px] font-bold text-white">PDF</span>
                    <span x-show="!isPdf(file)" class="rounded bg-blue-600 px-1 text-[10px] font-bold text-white">JPG</span>
                    <span x-text="file.name" class="max-w-[16rem] truncate"></span>
                    <button type="button" @click="removeFile(i)" aria-label="Remove file"
                            class="rounded-full p-1 text-slate-500 hover:bg-slate-300 hover:text-slate-900">
                        <x-heroicon-s-x-mark class="h-4 w-4" />
                    </button>
                </li>
            </template>
        </ul>
    </section>

    <p x-show="submitError" x-cloak x-text="submitError" class="text-sm font-medium text-red-600"></p>

    <button type="submit" :disabled="submitting"
            class="w-full rounded-lg bg-slate-900 py-4 text-lg font-bold uppercase tracking-wider text-white hover:bg-slate-700 disabled:opacity-60">
        <span x-text="submitting ? 'Submitting…' : 'Submit Claim'">Submit Claim</span>
    </button>
</form>
@endsection
