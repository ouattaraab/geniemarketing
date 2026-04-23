<x-layouts.public title="Confirmer le mot de passe — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="mx-auto max-w-md">
            <x-gm.section-heading subtitle="Zone sécurisée">
                Confirmer votre identité
            </x-gm.section-heading>

            <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
                <p class="text-sm text-gm-charcoal">
                    Cette action nécessite une confirmation de sécurité. Merci de saisir à nouveau
                    votre mot de passe pour continuer.
                </p>

                <form method="POST" action="{{ route('password.confirm') }}" class="mt-6 space-y-5">
                    @csrf

                    <div>
                        <label for="password" class="gm-meta block mb-2">Mot de passe <span class="text-gm-red">*</span></label>
                        <input id="password" name="password" type="password" required autofocus autocomplete="current-password"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('password')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="gm-btn-primary w-full">Confirmer</button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
