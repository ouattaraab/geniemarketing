<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="robots" content="noindex, nofollow" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>{{ $title ?? 'Backoffice' }} · GM Admin</title>

    <link rel="icon" type="image/png" href="/favicon.png" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="flex min-h-screen bg-gm-paper">
    {{-- Sidebar navigation BO --}}
    <aside class="flex w-64 flex-col border-r border-gm-gray-line bg-white">
        <div class="border-b border-gm-gray-line px-6 py-6">
            <a href="{{ route('admin.dashboard') }}" class="flex flex-col gap-1">
                <span class="font-slab text-xl font-bold italic leading-none text-gm-ink">
                    GM<span class="text-gm-red">.</span>Admin
                </span>
                <span class="font-mono text-[9px] uppercase tracking-[0.25em] text-gm-gray">
                    Backoffice éditorial
                </span>
            </a>
        </div>

        <nav class="flex-1 overflow-y-auto py-4">
            @php
                $sections = [
                    'Édition' => [
                        ['Articles', 'admin.articles.index', '📝'],
                        ['Médias', 'admin.media.index', '🖼️'],
                        ['Taxonomies', 'admin.taxonomies.index', '🏷️'],
                        ['Magazines', 'admin.issues.index', '📰'],
                    ],
                    'Commercial' => [
                        ['Abonnés', 'admin.subscribers.index', '👥'],
                        ['Commandes', 'admin.orders.index', '🧾'],
                        ['Newsletter', 'admin.newsletters.index', '📬'],
                    ],
                    'Système' => [
                        ['Utilisateurs', 'admin.users.index', '👤'],
                        ['Audit', 'admin.audit.index', '📋'],
                        ['Paramètres', 'admin.settings.index', '⚙️'],
                    ],
                ];
            @endphp

            @foreach ($sections as $sectionName => $items)
                <div class="mb-6">
                    <h3 class="mb-2 px-6 font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-gm-gray">
                        {{ $sectionName }}
                    </h3>
                    <ul>
                        @foreach ($items as [$label, $routeName, $icon])
                            @php
                                $active = \Illuminate\Support\Facades\Route::has($routeName) && request()->routeIs($routeName . '*');
                                $href = \Illuminate\Support\Facades\Route::has($routeName) ? route($routeName) : '#';
                            @endphp
                            <li>
                                <a
                                    href="{{ $href }}"
                                    class="flex items-center gap-3 border-l-2 px-6 py-2.5 text-sm transition-colors
                                        {{ $active ? 'border-gm-red bg-gm-red-soft font-bold text-gm-red' : 'border-transparent text-gm-charcoal hover:bg-gm-paper hover:text-gm-ink' }}"
                                >
                                    <span class="text-base">{{ $icon }}</span>
                                    <span>{{ $label }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <div class="border-t border-gm-gray-line px-6 py-4">
            @auth
                <div class="mb-3">
                    <div class="text-sm font-bold text-gm-ink">{{ auth()->user()->first_name ?? auth()->user()->name ?? '' }} {{ auth()->user()->last_name ?? '' }}</div>
                    <div class="gm-meta">{{ auth()->user()->getRoleNames()->first() ?? 'Utilisateur' }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="gm-meta hover:text-gm-red">Se déconnecter</button>
                </form>
            @endauth
        </div>
    </aside>

    <div class="flex flex-1 flex-col">
        {{-- Topbar --}}
        <header class="flex items-center justify-between border-b border-gm-gray-line bg-white px-8 py-4">
            <div>
                <h1 class="font-slab text-2xl font-bold italic text-gm-ink">
                    {{ $title ?? 'Backoffice' }}
                </h1>
                @isset($breadcrumb)
                    <div class="gm-meta mt-1">{{ $breadcrumb }}</div>
                @endisset
            </div>
            <div class="flex items-center gap-4">
                @isset($actions)
                    {{ $actions }}
                @endisset
            </div>
        </header>

        {{-- Contenu principal --}}
        <main class="flex-1 overflow-y-auto px-8 py-8">
            @if (session('status'))
                <div class="mb-6 border-l-4 border-gm-red bg-gm-red-soft px-4 py-3 text-sm text-gm-red-deep">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    {{-- Picker global toujours monté pour écouter "open-media-picker" depuis l'éditeur TipTap --}}
    <livewire:admin.media.media-picker />

    @livewireScripts
</body>
</html>
