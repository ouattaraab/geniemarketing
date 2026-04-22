<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    @php
        $pageTitle = $title ?? 'GÉNIE MARKETING Mag — Le magazine de référence du marketing ivoirien';
        $pageDesc = $description ?? 'GÉNIE MARKETING Mag : analyses, stratégies, succès stories et décryptages du marketing made in Côte d\'Ivoire.';
        $ogImageUrl = $ogImage ?? url('/og-default.png');
        $canonicalUrl = $canonical ?? url()->current();
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDesc }}" />
    <link rel="canonical" href="{{ $canonicalUrl }}" />

    {{-- Open Graph / Twitter Cards (EP-21) --}}
    <meta property="og:site_name" content="GÉNIE MARKETING Mag" />
    <meta property="og:title" content="{{ $pageTitle }}" />
    <meta property="og:description" content="{{ $pageDesc }}" />
    <meta property="og:type" content="{{ $ogType ?? 'website' }}" />
    <meta property="og:url" content="{{ $canonicalUrl }}" />
    <meta property="og:locale" content="fr_CI" />
    <meta property="og:image" content="{{ $ogImageUrl }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $pageTitle }}" />
    <meta name="twitter:description" content="{{ $pageDesc }}" />
    <meta name="twitter:image" content="{{ $ogImageUrl }}" />

    <link rel="icon" type="image/png" href="/favicon.png" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="gm-public flex min-h-screen flex-col">
    @if (\App\Models\Setting::get('promo.banner_enabled', false))
        <div class="bg-gm-red text-white">
            <div class="mx-auto flex max-w-container items-center justify-between gap-4 px-8 py-2 font-mono text-[11px] uppercase tracking-[0.12em]">
                <span>{{ \App\Models\Setting::get('promo.banner_text') }}</span>
                @if ($cta = \App\Models\Setting::get('promo.banner_cta'))
                    <a href="{{ \App\Models\Setting::get('promo.banner_url', '/abonnement') }}" class="underline font-bold hover:text-gm-paper">
                        {{ $cta }} →
                    </a>
                @endif
            </div>
        </div>
    @endif

    <x-gm.top-strip />
    <x-gm.header />

    <main class="flex-1">
        {{ $slot }}
    </main>

    <x-gm.footer />

    <x-gm.cookie-banner />

    @livewireScriptConfig
</body>
</html>
