@props(['bold', 'light' => null])
{{-- Page title: light + bold wording, thin logo-blue line with a small red tip (echoes the red in the logo). --}}
<div>
    <h1 class="text-4xl leading-tight tracking-tight text-[#263c76] md:text-5xl">
        @if ($light)<span class="font-light">{{ $light }}</span>@endif
        <span class="font-extrabold">{{ $bold }}</span>
    </h1>
    <div class="mt-3 flex items-center">
        <span class="h-[3px] w-28 rounded-full bg-[#263c76]"></span>
        <span class="-ml-1 h-[3px] w-8 rounded-full bg-red-600"></span>
    </div>
</div>
