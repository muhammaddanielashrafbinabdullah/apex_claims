{{-- Round status stamp. Needs Alpine's `selectedClaim` (status: pending | approved | rejected). --}}
<div class="pointer-events-none flex shrink-0 select-none items-center justify-center"
     :class="{ 'text-green-600': selectedClaim.status === 'approved', 'text-red-600': selectedClaim.status === 'rejected', 'text-amber-500': selectedClaim.status === 'pending' }">
    <svg viewBox="0 0 160 160" class="h-40 w-40 -rotate-12 opacity-90" role="img" :aria-label="'Claim ' + selectedClaim.status">
        <circle cx="80" cy="80" r="76" fill="none" stroke="currentColor" stroke-width="4"/>
        <circle cx="80" cy="80" r="68" fill="none" stroke="currentColor" stroke-width="1.5"/>
        <circle cx="80" cy="80" r="52" fill="none" stroke="currentColor" stroke-width="1" stroke-dasharray="2 4"/>
        <line x1="8" y1="56" x2="152" y2="56" stroke="currentColor" stroke-width="3"/>
        <line x1="8" y1="104" x2="152" y2="104" stroke="currentColor" stroke-width="3"/>
        <rect x="9" y="58" width="142" height="44" fill="white" opacity="0.001"/>
        <text x="80" y="92" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-weight="800" font-size="30"
              textLength="118" lengthAdjust="spacingAndGlyphs" fill="currentColor"
              x-text="selectedClaim.status.toUpperCase()"></text>
        <text x="80" y="40" text-anchor="middle" font-size="12" fill="currentColor" letter-spacing="6">★ ★ ★</text>
        <text x="80" y="132" text-anchor="middle" font-size="12" fill="currentColor" letter-spacing="6">★ ★ ★</text>
    </svg>
</div>
