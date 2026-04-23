<x-layouts.public title="Tableau de bord — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <x-gm.section-heading subtitle="{{ auth()->user()?->email }}">
            Tableau de bord
        </x-gm.section-heading>

        <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
            <p class="text-gm-charcoal">
                Vous êtes connecté. Accédez rapidement à votre espace :
            </p>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('account') }}" class="gm-btn-primary">Mon compte</a>
                <a href="{{ route('profile.edit') }}" class="border border-gm-ink px-5 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-ink hover:bg-gm-ink hover:text-white transition-colors">
                    Mon profil
                </a>
                <a href="{{ route('home') }}" class="border border-gm-gray-line px-5 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-charcoal hover:text-gm-red">
                    Retour au magazine
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
