<x-layouts.public title="Connexion — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="mx-auto max-w-md">
            <x-gm.section-heading subtitle="Accès abonnés">
                Se connecter
            </x-gm.section-heading>

            @if (session('status'))
                <div class="mb-6 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink">
                    {{ session('status') }}
                </div>
            @endif

            <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="gm-meta block mb-2">Email <span class="text-gm-red">*</span></label>
                        <input id="email" name="email" type="email" required autofocus autocomplete="username"
                               value="{{ old('email') }}"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('email')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="gm-meta block mb-2">Mot de passe <span class="text-gm-red">*</span></label>
                        <input id="password" name="password" type="password" required autocomplete="current-password"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('password')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <label class="flex items-center gap-2 font-sans text-sm text-gm-charcoal">
                        <input type="checkbox" name="remember" class="border-gm-gray-line text-gm-red focus:ring-gm-red" />
                        <span>Rester connecté</span>
                    </label>

                    <button type="submit" class="gm-btn-primary w-full">Se connecter</button>

                    @if (Route::has('password.request'))
                        <p class="gm-meta text-center normal-case tracking-normal">
                            <a href="{{ route('password.request') }}" class="hover:text-gm-red">
                                Mot de passe oublié ?
                            </a>
                        </p>
                    @endif
                </form>
            </div>

            <p class="gm-meta mt-8 text-center normal-case tracking-normal">
                Pas encore abonné ?
                <a href="{{ route('subscribe') }}" class="font-bold text-gm-red hover:text-gm-red-bright">
                    Découvrir nos formules
                </a>
            </p>
        </div>
    </section>
</x-layouts.public>
