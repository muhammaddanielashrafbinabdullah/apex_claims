<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign in – Apex Claims</title>
    <link href="https://fonts.bunny.net/css?family=inter:400,600,800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-900 font-sans">
    <form method="POST" action="{{ url('/login') }}" class="w-full max-w-sm space-y-5 rounded-xl bg-white p-8 shadow-2xl">
        @csrf
        <img src="{{ asset('images/logo.png') }}" alt="Apex Claims Portal" class="mx-auto h-auto w-56">

        <div>
            <label class="mb-1 block text-sm font-medium">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full rounded-md border-slate-300">
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Password</label>
            <input type="password" name="password" required class="w-full rounded-md border-slate-300">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" class="rounded"> Remember me</label>
        <button class="w-full rounded-md bg-slate-900 py-2.5 font-semibold text-white hover:bg-slate-700">Sign in</button>
    </form>
</body>
</html>
