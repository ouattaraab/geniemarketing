<x-layouts.public title="Vérifier votre email — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="mx-auto max-w-md">
            <x-gm.section-heading subtitle="Validation du compte">
                Vérification email
            </x-gm.section-heading>

            @if (session('status') == 'verification-link-sent')
                <div class="mb-6 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink">
                    Un nouveau lien de vérification vient d'être envoyé à l'adresse fournie à l'inscription.
                </div>
            @endif

            <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
                <p class="text-sm text-gm-charcoal">
                    Merci pour votre inscription. Avant de continuer, vérifiez votre adresse email
                    en cliquant sur le lien qui vous a été envoyé. Vous n'avez pas reçu l'email ?
                    Nous pouvons vous le renvoyer.
                </p>

                <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="gm-btn-primary w-full sm:w-auto">
                            Renvoyer le lien
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="gm-meta normal-case tracking-normal hover:text-gm-red">
                            Se déconnecter
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
