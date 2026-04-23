@props([
    'issueNumber' => 'Numéro 12',
    'issueDate' => null,
])

<header class="sticky top-0 z-40 border-b border-gm-gray-line bg-white shadow-[0_1px_0_rgba(0,0,0,0.03)]">
    <div class="mx-auto grid max-w-container grid-cols-[1fr_auto_1fr] items-center gap-8 px-8 py-6">
        <div class="gm-meta flex flex-col gap-1">
            <span class="font-bold text-gm-red">{{ $issueNumber }}</span>
            <span>{{ $issueDate ?? \Carbon\Carbon::now()->locale('fr')->translatedFormat('l j F Y') }}</span>
        </div>

        <a href="{{ route('home') }}" class="flex cursor-pointer select-none flex-col items-center gap-2" wire:navigate>
            <h1 class="font-slab text-4xl font-bold italic leading-none text-gm-ink md:text-5xl">
                GÉNIE <span class="text-gm-red">MARKETING</span>
            </h1>
            <span class="font-mono text-[9px] font-medium uppercase tracking-[0.3em] text-gm-charcoal-2">
                Le magazine du marketing ivoirien
            </span>
        </a>

        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('search') }}" class="gm-meta hover:text-gm-red" title="Recherche">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </a>
            @auth
                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            @click="open = !open"
                            @click.outside="open = false"
                            @keydown.escape.window="open = false"
                            class="gm-meta inline-flex items-center gap-1 hover:text-gm-red"
                            :aria-expanded="open"
                            aria-haspopup="true">
                        Mon compte
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.06l3.71-3.83a.75.75 0 111.08 1.04l-4.25 4.4a.75.75 0 01-1.08 0l-4.25-4.4a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak
                         x-transition.opacity.duration.100ms
                         class="absolute right-0 top-full z-50 mt-2 w-56 border border-gm-gray-line bg-white shadow-lg">
                        <a href="{{ route('account') }}"
                           class="block border-b border-gm-gray-line px-4 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.12em] text-gm-charcoal hover:bg-gm-paper hover:text-gm-red">
                            Mon abonnement
                        </a>
                        <a href="{{ route('profile.edit') }}"
                           class="block border-b border-gm-gray-line px-4 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.12em] text-gm-charcoal hover:bg-gm-paper hover:text-gm-red">
                            Mon profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="block">
                            @csrf
                            <button type="submit"
                                    class="block w-full text-left px-4 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.12em] text-gm-red hover:bg-gm-red-soft">
                                Se déconnecter
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <a href="{{ route('login') }}" class="gm-meta hover:text-gm-red">Se connecter</a>
            @endauth
            <a href="{{ route('subscribe') }}" class="gm-btn-primary">S'abonner</a>
        </div>
    </div>

    <nav class="border-t border-gm-gray-line bg-white">
        <div class="mx-auto flex max-w-container justify-center gap-0 px-8">
            @php
                $navItems = [
                    ['01', 'La Une',     route('home'),                              request()->routeIs('home')],
                    ['02', 'Analyses',   route('category.show', 'analyses'),         request()->is('rubriques/analyses*')],
                    ['03', 'Succès',     route('category.show', 'succes'),           request()->is('rubriques/succes*')],
                    ['04', 'Interviews', route('category.show', 'interviews'),       request()->is('rubriques/interviews*')],
                    ['05', 'Tribunes',   route('category.show', 'tribunes'),         request()->is('rubriques/tribunes*')],
                    ['06', 'Magazine',   route('magazine'),                          request()->routeIs('magazine')],
                ];
            @endphp
            @foreach ($navItems as [$num, $label, $href, $active])
                <a href="{{ $href }}" class="gm-nav-link {{ $active ? 'active' : '' }}">
                    <span class="mr-2 font-mono text-[10px] font-medium tracking-[0.05em] text-gm-gray">{{ $num }}</span>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </nav>
</header>
