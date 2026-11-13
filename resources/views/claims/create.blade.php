@extends('layouts.app')
@section('title', 'Submit New Claim – Apex')

@push('head')
<script>window.CLAIM_RATES = @json(config('claim_rates'));</script>
@endpush

@section('content')
<form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data"
      x-data="claimCalculator(@js(old('items', [])))" @submit="validate($event)" class="mx-auto max-w-6xl space-y-6">
    @csrf

    <h1 class="text-white text-left text-5xl font-extrabold tracking-tight">CLAIM OF {{ $period }}</h1>

    @if ($errors->any())
        <div class="rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-800">
            <ul class="list-inside list-disc">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    {{-- Employee info (all boxes start empty) --}}
    <section class="overflow-hidden rounded-lg border-2 border-slate-800 bg-white">
<div class="bg-slate-800 px-4 py-2 text-left font-bold text-white">Claimant Details</div>
<div class="p-6">
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
    </div>
</section>

    {{-- Claims table: 7 columns --}}
    <section class="overflow-x-auto rounded-lg border-2 border-slate-800 bg-white">
        <table class="w-full min-w-[60rem] table-fixed border-collapse text-sm">
            <colgroup>
                <col class="w-60"><col class="w-36"><col class="w-40"><col class="w-44"><col class="w-40"><col class="w-12"><col class="w-32">
            </colgroup>
            <thead>
                <tr><th colspan="7" class="bg-slate-800 px-4 py-2 text-left text-white">Types of Claims allowed</th></tr>
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
                            <td colspan="2" class="px-2 py-2">
                                <div class="flex items-center justify-center gap-2">
                                    <input type="number" step="0.01" min="0" max="{{ $row['max'] }}"
                                           name="items[{{ $row['key'] }}]" x-model.number="form.{{ $row['key'] }}" @wheel="$el.blur()"
                                           class="w-28 min-w-0 rounded-none border-slate-500 py-1 text-right">
                                    <span class="text-slate-600">km</span>
                                </div>
                            </td>
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
    <section class="overflow-hidden rounded-lg border-2 border-slate-800 bg-white">
<div class="bg-slate-800 px-4 py-2 text-left font-bold text-white">Receipts & Proof</div>
<div class="p-6">

        <div class="flex items-center gap-3">
            <label class="relative cursor-pointer rounded border border-slate-600 bg-slate-100 px-4 py-2 text-sm font-semibold hover:bg-slate-200">
                Choose File
                <input type="file" name="attachments[]" x-ref="fileInput" multiple accept=".pdf,.jpg,.jpeg,application/pdf,image/jpeg"
                       class="sr-only" @change="addFiles($event)">
            </label>
            <span class="text-sm text-slate-500" x-text="fileStatus">No file chosen</span>
        </div>
        <p class="mt-2 text-xs text-slate-500">PDF or JPG, up to 10 MB each. Attach as many files as needed.</p>

        <p x-show="fileError" x-cloak x-text="fileError" class="mt-2 text-sm text-red-600"></p>

        <ul class="mt-4 flex flex-col gap-2">
            <template x-for="(file, i) in files" :key="file.name + file.size">
                <li class="flex h-14 w-full max-w-xl items-center gap-1 rounded-full border border-slate-400 bg-slate-100 pl-2 pr-2 text-sm">
                    <button type="button" @click="openPreview(file)" title="Click to preview this file"
                            class="flex h-11 min-w-0 flex-1 items-center gap-3 rounded-full pl-1 pr-3 text-left hover:bg-slate-200">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded bg-white">
                            <img x-show="!isPdf(file)" :src="isPdf(file) ? '' : urlFor(file)" alt="" class="h-full w-full object-cover">
                            <span x-show="isPdf(file)" class="flex h-full w-full items-center justify-center bg-red-600 text-[10px] font-bold text-white">PDF</span>
                        </span>
                        <span class="w-11 shrink-0 rounded py-0.5 text-center text-[10px] font-bold text-white"
                              :class="isPdf(file) ? 'bg-red-600' : 'bg-blue-600'" x-text="isPdf(file) ? 'PDF' : 'JPG'"></span>
                        <span x-text="file.name" class="min-w-0 flex-1 truncate underline decoration-dotted"></span>
                    </button>
                    <button type="button" @click="removeFile(i)" aria-label="Remove file"
                            class="shrink-0 rounded-full p-1 text-slate-500 hover:bg-slate-300 hover:text-slate-900">
                        <x-heroicon-s-x-mark class="h-4 w-4" />
                    </button>
                </li>
            </template>
        </ul>
    </div>
</section>

    <p x-show="submitError" x-cloak x-text="submitError" class="rounded-lg bg-white px-4 py-2 text-sm font-medium text-red-600"></p>

    <button type="submit" :disabled="submitting"
            class="w-full rounded-lg bg-white py-4 text-lg font-bold uppercase tracking-wider text-[#263c76] hover:bg-slate-100 disabled:opacity-60">
        <span x-text="submitting ? 'Submitting…' : 'Submit Claim'">Submit Claim</span>
    </button>
    {{-- File preview pop-up (shows the chosen file before it is submitted) --}}
    <div x-show="previewFile" x-cloak x-transition.opacity @keydown.escape.window="closePreview()"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/70 p-4" @click.self="closePreview()">
        <div class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl" @click.stop>
            <div class="flex items-center justify-between border-b px-5 py-3">
                <span class="truncate font-semibold" x-text="previewFile ? previewFile.name : ''"></span>
                <button type="button" @click="closePreview()" class="rounded-full p-1.5 hover:bg-slate-200" aria-label="Close preview">
                    <x-heroicon-o-x-mark class="h-6 w-6" />
                </button>
            </div>
            <div class="flex min-h-0 flex-1 items-center justify-center overflow-auto bg-slate-100 p-3">
                <template x-if="previewFile && !isPdf(previewFile)">
                    <img :src="previewUrl" alt="Preview" class="max-h-[78vh] max-w-full object-contain">
                </template>
                <template x-if="previewFile && isPdf(previewFile)">
                    <iframe :src="previewUrl" class="h-[78vh] w-full bg-white" title="PDF preview"></iframe>
                </template>
            </div>
        </div>
    </div>
</form>
@endsection
