<x-layouts.public title="Mot de passe oublié — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="mx-auto max-w-md">
            <x-gm.section-heading subtitle="Récupération">
                Mot de passe oublié
            </x-gm.section-heading>

            @if (session('status'))
                <div class="mb-6 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink">
                    {{ session('status') }}
                </div>
            @endif

            <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
                <p class="text-sm text-gm-charcoal">
                    Pas de souci. Indiquez votre email, nous vous enverrons un lien de
                    réinitialisation pour choisir un nouveau mot de passe.
                </p>

                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="gm-meta block mb-2">Email <span class="text-gm-red">*</span></label>
                        <input id="email" name="email" type="email" required autofocus
                               value="{{ old('email') }}"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('email')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="gm-btn-primary w-full">Envoyer le lien</button>
                </form>
            </div>

            <p class="gm-meta mt-8 text-center normal-case tracking-normal">
                <a href="{{ route('login') }}" class="hover:text-gm-red">← Retour à la connexion</a>
            </p>
        </div>
    </section>
</x-layouts.public>
