<x-layouts.public title="Vérification — GM">
    <section class="mx-auto max-w-container-narrow px-8 py-24">
        <div class="border border-gm-red bg-white p-8">
            <span class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red">Vérification de sécurité</span>
            <h1 class="mt-3 font-slab text-2xl font-bold italic text-gm-ink md:text-3xl">
                Double authentification
            </h1>
            <p class="mt-3 text-gm-charcoal">
                Saisissez le code à 6 chiffres de votre application d'authentification
                (ou un code de récupération à usage unique).
            </p>

            <form method="POST" action="{{ route('2fa.verify') }}" class="mt-6 space-y-4">
                @csrf
                <input
                    type="text"
                    name="code"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    required
                    autofocus
                    class="w-full border border-gm-gray-line px-4 py-3 font-mono text-2xl tracking-[0.3em] text-center focus:border-gm-red focus:ring-0"
                    placeholder="123456"
                />
                @error('code')<p class="gm-meta text-gm-red">{{ $message }}</p>@enderror

                <button type="submit" class="gm-btn-primary w-full">Vérifier</button>
            </form>

            <p class="gm-meta mt-6 text-center">
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-2fa').submit();"
                   class="hover:text-gm-red">Se déconnecter</a>
            </p>
            <form id="logout-2fa" method="POST" action="{{ route('logout') }}" class="hidden">
                @csrf
            </form>
        </div>
    </section>
</x-layouts.public>
