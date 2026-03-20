<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @include('layouts.partials.assets')
</head>
<body>
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -top-44 left-1/2 h-96 w-96 -translate-x-1/2 rounded-full bg-cyan-200/45 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-80 w-80 translate-x-1/3 translate-y-1/3 rounded-full bg-indigo-200/40 blur-3xl"></div>
    </div>

    <header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/80 backdrop-blur-lg">
        <div class="shell flex items-center justify-between py-4">
            <a class="text-sm font-extrabold tracking-[0.16em] text-slate-900" href="{{ route('pages.home') }}">
                {{ strtoupper(config('app.name')) }}
            </a>

            <nav class="flex items-center gap-2">
                <a href="{{ route('pages.home') }}" class="btn btn-secondary">Home</a>
                @auth
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-primary">Admin Builder</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary">Logout</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="btn btn-primary">Login</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="shell space-y-4 pb-16 pt-8">
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <p class="font-semibold">Controlla i campi:</p>
                <ul class="mt-2 list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
