<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title') — GÉNIE MARKETING Mag</title>
    <meta name="robots" content="noindex, nofollow" />
    @vite(['resources/css/app.css'])
</head>
<body class="gm-public flex min-h-screen flex-col bg-gm-paper">
    <div class="mx-auto flex min-h-screen max-w-container-narrow flex-col items-center justify-center px-8 py-16 text-center">
        <span class="font-mono text-[11px] font-bold uppercase tracking-[0.2em] text-gm-red">
            @yield('code')
        </span>
        <h1 class="mt-4 font-slab text-4xl font-bold italic leading-tight text-gm-ink md:text-6xl">
            @yield('message')
        </h1>
        <p class="mt-6 max-w-lg text-gm-charcoal">
            @yield('description')
        </p>

        <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="gm-btn-primary">Retour à l'accueil</a>
            <a href="{{ url('/recherche') }}" class="gm-meta hover:text-gm-red">Lancer une recherche</a>
        </div>

        <div class="mt-16 font-slab text-2xl font-bold italic text-gm-ink">
            GÉNIE <span class="text-gm-red">MARKETING</span>
        </div>
        <p class="gm-meta mt-2">Le magazine du marketing ivoirien</p>
    </div>
</body>
</html>
