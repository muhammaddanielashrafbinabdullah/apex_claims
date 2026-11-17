<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in – Apex Claims</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600,800|montserrat:500,600,700&display=swap" rel="stylesheet">
    <style>
        [x-cloak]{display:none !important}
        .mont{font-family:'Montserrat','Inter',ui-sans-serif,system-ui,sans-serif}
        /* Card fades in when the page opens */
        .cardIn{animation:cardIn .8s cubic-bezier(.2,.7,.2,1) both}
        @keyframes cardIn{from{opacity:0;transform:translateY(14px) scale(.985)}to{opacity:1;transform:none}}
        @keyframes spin{to{transform:rotate(360deg)}}
        @media (prefers-reduced-motion:reduce){.cardIn{animation:none}}
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="mont relative flex min-h-screen items-center justify-center overflow-hidden bg-slate-200 p-4 font-sans">
<svg viewBox="0 0 1440 900" preserveAspectRatio="xMidYMid slice" class="pointer-events-none absolute inset-0 h-full w-full" aria-hidden="true">
<defs>
 <pattern id="dots" width="26" height="26" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.3" fill="#263c76" opacity=".16"/></pattern>
 <linearGradient id="pg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#eef2fb"/><stop offset=".55" stop-color="#d9e2f4"/><stop offset="1" stop-color="#bccbe9"/></linearGradient>
 <radialGradient id="redGlow" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#d9262c" stop-opacity=".16"/><stop offset="1" stop-color="#d9262c" stop-opacity="0"/></radialGradient>
 <radialGradient id="blueGlow" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#263c76" stop-opacity=".14"/><stop offset="1" stop-color="#263c76" stop-opacity="0"/></radialGradient>
 <filter id="grain1" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency=".85" numOctaves="2" stitchTiles="stitch"/><feColorMatrix type="saturate" values="0"/></filter>
</defs>
<rect width="1440" height="900" fill="url(#pg)"/>
<circle cx="1280" cy="820" r="380" fill="url(#redGlow)"/>
<circle cx="120" cy="80" r="420" fill="url(#blueGlow)"/>
<g ><rect x="-60" y="-60" width="1560" height="1020" fill="url(#dots)"/></g>
<g  fill="none" stroke="#263c76" stroke-width="1.2" opacity=".13"><circle cx="0" cy="0" r="180"/><circle cx="0" cy="0" r="260"/><circle cx="0" cy="0" r="340"/><circle cx="0" cy="0" r="420"/><circle cx="1440" cy="900" r="200"/><circle cx="1440" cy="900" r="290"/><circle cx="1440" cy="900" r="380"/><circle cx="1440" cy="900" r="470"/></g>
<g  fill="none" stroke="#d9262c" stroke-width="1.2" opacity=".30"><circle cx="1440" cy="900" r="335" stroke-dasharray="3 9"/><circle cx="0" cy="0" r="300" stroke-dasharray="2 12"/></g>
<g  fill="none" stroke="#263c76" stroke-width="1" opacity=".13"><path d="M-40 700 C300 560 600 820 900 640 S1300 560 1480 600"/><path d="M-40 730 C300 590 600 850 900 670 S1300 590 1480 630"/><path d="M-40 760 C300 620 600 880 900 700 S1300 620 1480 660"/></g>
<g ><path d="M-40 790 C300 650 600 910 900 730 S1300 650 1480 690" fill="none" stroke="#d9262c" stroke-width="1.5" opacity=".5"/><path d="M-40 150 C300 260 600 60 900 190 S1300 250 1480 120" fill="none" stroke="#d9262c" stroke-width="1.2" opacity=".28"/></g>
<g  fill="#d9262c" opacity=".6"><circle cx="210" cy="120" r="3.2"/><circle cx="1230" cy="780" r="3.2"/><circle cx="1330" cy="150" r="2.6"/><circle cx="120" cy="640" r="2.6"/><circle cx="760" cy="60" r="2.2"/><circle cx="1050" cy="860" r="2.2"/></g>
<rect width="1440" height="900" filter="url(#grain1)" opacity=".07" style="mix-blend-mode:multiply"/>
</svg>
    {{-- Wave shape for the left picture (units 0-1 of the panel) --}}
    <svg width="0" height="0" class="absolute" aria-hidden="true">
        <defs>
            <clipPath id="wave" clipPathUnits="objectBoundingBox">
                <path d="M0,0 L0.86,0 C0.80,0.14 0.72,0.26 0.78,0.40 C0.86,0.56 0.98,0.64 0.87,0.80 C0.81,0.89 0.85,0.95 0.80,1 L0,1 Z"/>
            </clipPath>
        </defs>
    </svg>

    <div class="cardIn relative z-10 w-full max-w-4xl overflow-hidden rounded-3xl bg-[#263c76] shadow-2xl md:h-[560px]">
        {{-- Left: telecom silhouette picture with the logo (hidden on small screens) --}}
        <div class="absolute inset-y-0 left-0 hidden w-[62%] md:block" style="filter: drop-shadow(6px 0 10px rgba(8,15,40,.45));">
            <div class="relative h-full w-full overflow-hidden" style="clip-path: url(#wave);">
<svg viewBox="0 0 620 560" preserveAspectRatio="xMidYMid slice" class="absolute inset-0 h-full w-full" aria-hidden="true">
<defs>
 <linearGradient id="bg" x1="0" y1="0" x2="0.35" y2="1"><stop offset="0" stop-color="#5a7fd1"/><stop offset=".5" stop-color="#2f4b9a"/><stop offset="1" stop-color="#16275a"/></linearGradient>
 <radialGradient id="gl" cx=".35" cy=".3" r=".6"><stop offset="0" stop-color="#fff" stop-opacity=".35"/><stop offset="1" stop-color="#fff" stop-opacity="0"/></radialGradient>
 <linearGradient id="fog" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#5a7fd1" stop-opacity="0"/><stop offset="1" stop-color="#9fbbf0" stop-opacity=".35"/></linearGradient>
<filter id="grain2" x="0" y="0" width="100%" height="100%"><feTurbulence type="fractalNoise" baseFrequency=".85" numOctaves="2" stitchTiles="stitch"/><feColorMatrix type="saturate" values="0"/></filter>
</defs>
<rect width="620" height="560" fill="url(#bg)"/>
<rect width="620" height="560" fill="url(#gl)"/>
<g fill="#fff"><path opacity=".06" d="M0 330 C160 240 300 430 620 250 V560 H0Z"/><path opacity=".05" d="M0 400 C180 330 320 480 620 340 V560 H0Z"/></g>
<g fill="none" stroke="#fff" stroke-width="1" opacity=".16"><path d="M0 300 C160 210 300 400 620 220"/><path d="M0 316 C160 226 300 416 620 238"/><path d="M0 332 C160 242 300 432 620 256"/><path d="M0 348 C160 258 300 448 620 274"/></g>
<path d="M0 365 C160 275 300 465 620 285" fill="none" stroke="#e23b3b" stroke-width="1.4" opacity=".6"/><path d="M0 392 C160 302 300 492 620 312" fill="none" stroke="#e23b3b" stroke-width="1" opacity=".3"/>
<g fill="#8fb0ee" opacity="0.28"><rect x="0" y="471" width="22" height="89"/><rect x="22" y="422" width="14" height="138"/><rect x="28.0" y="410" width="2" height="12"/><rect x="40" y="426" width="14" height="134"/><rect x="46.0" y="414" width="2" height="12"/><rect x="54" y="437" width="26" height="123"/><rect x="66.0" y="425" width="2" height="12"/><rect x="80" y="436" width="30" height="124"/><rect x="94.0" y="424" width="2" height="12"/><rect x="114" y="462" width="14" height="98"/><rect x="132" y="417" width="14" height="143"/><rect x="146" y="485" width="18" height="75"/><rect x="164" y="437" width="22" height="123"/><rect x="174.0" y="425" width="2" height="12"/><rect x="186" y="451" width="30" height="109"/><rect x="220" y="477" width="18" height="83"/><rect x="242" y="443" width="18" height="117"/><rect x="250.0" y="431" width="2" height="12"/><rect x="264" y="418" width="14" height="142"/><rect x="270.0" y="406" width="2" height="12"/><rect x="278" y="403" width="26" height="157"/><rect x="306" y="416" width="26" height="144"/><rect x="334" y="459" width="22" height="101"/><rect x="360" y="480" width="18" height="80"/><rect x="382" y="447" width="26" height="113"/><rect x="410" y="481" width="30" height="79"/><rect x="424.0" y="469" width="2" height="12"/><rect x="442" y="394" width="18" height="166"/><rect x="462" y="485" width="26" height="75"/><rect x="488" y="417" width="30" height="143"/><rect x="520" y="402" width="22" height="158"/><rect x="544" y="432" width="30" height="128"/><rect x="558.0" y="420" width="2" height="12"/><rect x="574" y="430" width="22" height="130"/><rect x="596" y="397" width="14" height="163"/><rect x="614" y="403" width="30" height="157"/></g>
<g fill="none" stroke="#fff" stroke-width="1" opacity=".28"><path d="M97,296 Q78.5,242.5 60,225"/><path d="M60,225 Q105.0,169.5 150,150"/><path d="M150,150 Q195.0,177.0 240,240"/><path d="M240,240 Q270.0,157.0 300,110"/><path d="M300,110 Q365.0,124.5 430,175"/><path d="M430,175 Q485.0,117.0 540,95"/><path d="M430,175 Q465.0,199.5 500,260"/><path d="M500,260 Q520.0,304.5 540,385"/><path d="M240,240 Q288.0,268.0 431,332"/><path d="M431,332 Q358.0,314.0 475,332"/><path d="M475,332 Q440.0,278.0 500,260"/><path d="M97,296 Q148.5,340.0 200,420"/><path d="M200,420 Q245.0,407.0 290,430"/><path d="M290,430 Q313.0,363.0 431,332"/><path d="M475,332 Q415.0,353.0 450,410"/><path d="M450,410 Q495.0,379.5 540,385"/><path d="M300,110 Q318.0,203.0 431,332"/></g>
<g fill="#fff" opacity=".55"><circle cx="97" cy="296" r="2.4"/><circle cx="431" cy="332" r="2.4"/><circle cx="380" cy="332" r="2.4"/><circle cx="200" cy="420" r="2.4"/><circle cx="290" cy="430" r="2.4"/><circle cx="450" cy="410" r="2.4"/><circle cx="540" cy="385" r="2.4"/><circle cx="150" cy="150" r="2.4"/><circle cx="300" cy="110" r="2.4"/><circle cx="430" cy="175" r="2.4"/><circle cx="540" cy="95" r="2.4"/><circle cx="60" cy="225" r="2.4"/><circle cx="240" cy="240" r="2.4"/><circle cx="500" cy="260" r="2.4"/></g><circle cx="431" cy="332" r="2.6" fill="#e23b3b" opacity=".85"/><circle cx="300" cy="110" r="2.6" fill="#e23b3b" opacity=".85"/><circle cx="60" cy="225" r="2.6" fill="#e23b3b" opacity=".85"/>
<g fill="#3d5cae" opacity="0.55"><rect x="0" y="476" width="26" height="84"/><rect x="12.0" y="464" width="2" height="12"/><rect x="28" y="499" width="22" height="61"/><rect x="52" y="493" width="14" height="67"/><rect x="66" y="470" width="18" height="90"/><rect x="86" y="499" width="14" height="61"/><rect x="104" y="503" width="22" height="57"/><rect x="130" y="467" width="22" height="93"/><rect x="156" y="491" width="26" height="69"/><rect x="168.0" y="479" width="2" height="12"/><rect x="182" y="491" width="18" height="69"/><rect x="200" y="497" width="26" height="63"/><rect x="226" y="467" width="18" height="93"/><rect x="248" y="480" width="30" height="80"/><rect x="282" y="514" width="30" height="46"/><rect x="316" y="470" width="30" height="90"/><rect x="348" y="459" width="14" height="101"/><rect x="362" y="512" width="18" height="48"/><rect x="382" y="506" width="18" height="54"/><rect x="400" y="520" width="14" height="40"/><rect x="418" y="474" width="14" height="86"/><rect x="432" y="472" width="18" height="88"/><rect x="440.0" y="460" width="2" height="12"/><rect x="452" y="474" width="22" height="86"/><rect x="474" y="461" width="26" height="99"/><rect x="502" y="502" width="14" height="58"/><rect x="508.0" y="490" width="2" height="12"/><rect x="518" y="459" width="22" height="101"/><rect x="540" y="518" width="30" height="42"/><rect x="554.0" y="506" width="2" height="12"/><rect x="574" y="502" width="22" height="58"/><rect x="596" y="482" width="30" height="78"/></g>
<g fill="#16275a" opacity=".92"><g transform="translate(95 0)"><polygon points="321,560 324,490 327,410 330,364 342,364 345,410 348,490 351,560"/><rect x="334.8" y="332" width="2.4" height="34"/><rect x="328" y="390" width="16" height="3"/><polygon points="365,560 368,490 371,410 374,364 386,364 389,410 392,490 395,560"/><rect x="378.8" y="332" width="2.4" height="34"/><rect x="372" y="390" width="16" height="3"/><rect x="338" y="440" width="40" height="7"/></g><polygon points="92,560 95,440 96,332 98,332 99,440 102,560"/><ellipse cx="97" cy="360" rx="9" ry="7"/><rect x="95.8" y="298" width="2.4" height="40"/></g>
<rect y="330" width="620" height="230" fill="url(#fog)"/>
<g fill="#13224f" opacity="0.9"><rect x="0" y="505" width="22" height="55"/><rect x="22" y="524" width="22" height="36"/><rect x="48" y="498" width="22" height="62"/><rect x="58.0" y="486" width="2" height="12"/><rect x="70" y="513" width="18" height="47"/><rect x="88" y="505" width="18" height="55"/><rect x="110" y="537" width="14" height="23"/><rect x="126" y="526" width="22" height="34"/><rect x="150" y="492" width="26" height="68"/><rect x="178" y="524" width="14" height="36"/><rect x="184.0" y="512" width="2" height="12"/><rect x="194" y="517" width="18" height="43"/><rect x="202.0" y="505" width="2" height="12"/><rect x="216" y="538" width="30" height="22"/><rect x="250" y="497" width="22" height="63"/><rect x="260.0" y="485" width="2" height="12"/><rect x="276" y="514" width="14" height="46"/><rect x="290" y="527" width="26" height="33"/><rect x="320" y="533" width="22" height="27"/><rect x="346" y="509" width="26" height="51"/><rect x="372" y="528" width="18" height="32"/><rect x="390" y="501" width="18" height="59"/><rect x="412" y="499" width="18" height="61"/><rect x="432" y="529" width="22" height="31"/><rect x="454" y="538" width="14" height="22"/><rect x="472" y="505" width="14" height="55"/><rect x="486" y="526" width="26" height="34"/><rect x="512" y="522" width="14" height="38"/><rect x="518.0" y="510" width="2" height="12"/><rect x="530" y="490" width="18" height="70"/><rect x="550" y="512" width="30" height="48"/><rect x="580" y="509" width="22" height="51"/><rect x="606" y="506" width="26" height="54"/><rect x="618.0" y="494" width="2" height="12"/></g>
<rect width="620" height="560" filter="url(#grain2)" opacity=".08" style="mix-blend-mode:overlay"/>
</svg>
                <div class="absolute left-[42%] top-[52%] w-72 -translate-x-1/2 -translate-y-1/2 rounded-2xl bg-white p-5 shadow-[0_18px_40px_rgba(15,25,60,.35)]">
                    <img src="{{ asset('images/logo.png') }}" alt="Apex Claims Portal" class="h-auto w-full">
                </div>
            </div>
        </div>

        {{-- Right: only email, password and the Login button --}}
        <div class="flex min-h-[420px] items-center md:ml-auto md:w-[42%] md:min-h-0 md:h-full">
            <form method="POST" action="{{ url('/login') }}" x-data="{ busy: false, show: false, caps: false }" @pageshow.window="busy = false" @submit="busy = true" class="w-full space-y-6 px-8 py-10 md:px-9">
                @csrf
                <div>
                    <label class="mont mb-2 block text-sm font-semibold tracking-wide text-blue-100">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-full border border-blue-200/60 bg-transparent px-5 py-2.5 text-white placeholder-blue-200/50 transition duration-200 focus:border-white focus:bg-white/5 focus:ring-4 focus:ring-white/20">
                    @error('email') <p class="mt-2 text-sm text-red-300">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mont mb-2 block text-sm font-semibold tracking-wide text-blue-100">Password</label>
                    <div class="relative">
                        <input :type="show ? 'text' : 'password'" type="password" name="password" required @keyup="caps = $event.getModifierState && $event.getModifierState('CapsLock')" @keydown="caps = $event.getModifierState && $event.getModifierState('CapsLock')"
                               class="w-full rounded-full border border-blue-200/60 bg-transparent py-2.5 pl-5 pr-12 text-white placeholder-blue-200/50 transition duration-200 focus:border-white focus:bg-white/5 focus:ring-4 focus:ring-white/20">
                        <button type="button" @click="show = !show" tabindex="-1" class="absolute inset-y-0 right-4 flex items-center text-blue-200/80 transition hover:text-white" :aria-label="show ? 'Hide password' : 'Show password'">
                            <x-heroicon-o-eye class="h-5 w-5" x-show="!show" />
                            <x-heroicon-o-eye-slash class="h-5 w-5" x-show="show" x-cloak />
                        </button>
                    </div>
                    <p x-show="caps" x-cloak class="mt-2 text-xs text-blue-200/80">Caps Lock is on</p>
                </div>
                <button :disabled="busy" class="mont flex w-full items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-100 to-white py-3 font-bold tracking-wide text-[#263c76] shadow-lg transition duration-200 hover:-translate-y-0.5 hover:shadow-xl hover:brightness-95 active:translate-y-0 disabled:opacity-80">
                    <svg x-show="busy" x-cloak class="h-4 w-4" viewBox="0 0 24 24" fill="none" style="animation:spin .8s linear infinite"><circle cx="12" cy="12" r="9" stroke="#263c76" stroke-opacity=".25" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="#263c76" stroke-width="3" stroke-linecap="round"/></svg>
                    <span x-text="busy ? 'Signing in…' : 'Login'">Login</span>
                </button>
            </form>
        </div>
    </div>
</body>
</html>
