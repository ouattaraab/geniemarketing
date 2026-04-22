<x-layouts.public title="Double authentification — GM">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="gm-section-heading flex items-baseline gap-6">
            <span class="font-mono text-xs font-bold tracking-[0.15em] text-gm-red">Sécurité</span>
            <h1 class="font-slab text-3xl font-bold italic leading-none text-gm-ink md:text-5xl">
                Double authentification
            </h1>
        </div>

        @if (session('status'))
            <div class="mb-8 border-l-4 border-gm-red bg-gm-red-soft px-4 py-3 text-sm text-gm-red-deep">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-10 grid gap-8 md:grid-cols-[1fr_1fr]">
            <div class="border border-gm-gray-line bg-white p-6">
                <h2 class="font-slab text-xl font-bold italic text-gm-ink">1. Scannez le QR code</h2>
                <p class="gm-meta mt-2 normal-case tracking-normal">
                    Avec Google Authenticator, Microsoft Authenticator, 1Password ou votre gestionnaire TOTP préféré.
                </p>
                <div class="mt-6 flex justify-center bg-white p-4">
                    {!! $qrSvg !!}
                </div>
                <p class="gm-meta mt-4 text-center">
                    Clé manuelle : <code class="bg-gm-cream px-2 py-0.5 font-mono text-xs">{{ $secret }}</code>
                </p>
            </div>

            <div class="border border-gm-gray-line bg-white p-6">
                <h2 class="font-slab text-xl font-bold italic text-gm-ink">2. Confirmez avec un code</h2>
                <p class="gm-meta mt-2 normal-case tracking-normal">
                    Saisissez le code à 6 chiffres affiché par votre application.
                </p>

                <form method="POST" action="{{ route('2fa.enable') }}" class="mt-6 space-y-4">
                    @csrf
                    <input
                        type="text"
                        name="code"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        maxlength="6"
                        required
                        autofocus
                        class="w-full border border-gm-gray-line px-4 py-3 font-mono text-2xl tracking-[0.4em] text-center focus:border-gm-red focus:ring-0"
                        placeholder="123456"
                    />
                    @error('code')<p class="gm-meta text-gm-red">{{ $message }}</p>@enderror

                    <button type="submit" class="gm-btn-primary w-full">
                        {{ $alreadyEnabled ? 'Régénérer' : 'Activer la 2FA' }}
                    </button>
                </form>

                @if ($alreadyEnabled && count($recoveryCodes) > 0)
                    <div class="mt-8 border-t border-gm-gray-line pt-6">
                        <h3 class="font-slab text-lg font-bold italic text-gm-ink">Codes de récupération</h3>
                        <p class="gm-meta mt-1 normal-case tracking-normal">
                            Conservez ces codes dans un endroit sûr. Chaque code n'est utilisable qu'une fois.
                        </p>
                        <ul class="mt-4 grid grid-cols-2 gap-2 font-mono text-sm">
                            @foreach ($recoveryCodes as $c)
                                <li class="border border-gm-gray-line bg-gm-paper px-3 py-2 text-center">{{ $c }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </section>
</x-layouts.public>
