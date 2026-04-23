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
                // Badge temps-réel sur le nombre de commentaires en attente.
                // Cache 60 s pour éviter un COUNT à chaque pageload BO.
                $pendingComments = \Illuminate\Support\Facades\Cache::remember(
                    'admin.comments.pending_count',
                    60,
                    fn () => \App\Models\Comment::where('status', 'pending')->count(),
                );

                // Rôles autorisés par item. En accord avec :
                // - Livewire composants admin (Comments, Orders, Subscribers, Users, Audit, Settings)
                // - Le seeder RolesSeeder.
                // Omis = tous les rôles BO.
                $sections = [
                    'Édition' => [
                        ['Articles', 'admin.articles.index', '📝', null, null],
                        ['Médias', 'admin.media.index', '🖼️', null, null],
                        ['Taxonomies', 'admin.taxonomies.index', '🏷️', null, null],
                        ['Magazines', 'admin.issues.index', '📰', null, ['chef', 'edit', 'adm', 'sup']],
                        ['Commentaires', 'admin.comments.index', '💬', $pendingComments > 0 ? $pendingComments : null, ['chef', 'edit', 'adm', 'sup']],
                    ],
                    'Commercial' => [
                        ['Formules', 'admin.plans.index', '💳', null, ['com', 'adm', 'sup']],
                        ['Abonnés', 'admin.subscribers.index', '👥', null, ['com', 'adm', 'sup']],
                        ['Commandes', 'admin.orders.index', '🧾', null, ['com', 'adm', 'sup']],
                        ['Bannières pub', 'admin.ads.index', '📣', null, ['com', 'adm', 'sup']],
                        ['Newsletter', 'admin.newsletters.index', '📬', null, ['com', 'adm', 'sup']],
                    ],
                    'Système' => [
                        ['Utilisateurs', 'admin.users.index', '👤', null, ['adm', 'sup']],
                        ['Moyens de paiement', 'admin.payment-methods.index', '🔌', null, ['adm', 'sup']],
                        ['Audit', 'admin.audit.index', '📋', null, ['adm', 'sup']],
                        ['Paramètres', 'admin.settings.index', '⚙️', null, ['adm', 'sup']],
                    ],
                ];

                $currentUser = auth()->user();
                $canSee = fn (?array $roles) => $roles === null || ($currentUser?->hasAnyRole($roles) ?? false);
            @endphp

            @foreach ($sections as $sectionName => $items)
                @php $visibleItems = collect($items)->filter(fn ($i) => $canSee($i[4] ?? null)); @endphp
                @if ($visibleItems->isNotEmpty())
                    <div class="mb-6">
                        <h3 class="mb-2 px-6 font-mono text-[10px] font-bold uppercase tracking-[0.2em] text-gm-gray">
                            {{ $sectionName }}
                        </h3>
                        <ul>
                            @foreach ($visibleItems as [$label, $routeName, $icon, $badge])
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
                                        <span class="flex-1">{{ $label }}</span>
                                        @if ($badge !== null)
                                            <span class="inline-flex min-w-[1.5rem] items-center justify-center rounded-full bg-gm-red px-1.5 text-[10px] font-bold text-white">
                                                {{ $badge }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
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
