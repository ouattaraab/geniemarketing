<x-layouts.public title="Créer un compte — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="mx-auto max-w-md">
            <x-gm.section-heading subtitle="Inscription">
                Créer un compte
            </x-gm.section-heading>

            <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="first_name" class="gm-meta block mb-2">Prénom <span class="text-gm-red">*</span></label>
                            <input id="first_name" name="first_name" type="text" required autofocus autocomplete="given-name"
                                   value="{{ old('first_name') }}"
                                   class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                            @error('first_name')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="last_name" class="gm-meta block mb-2">Nom <span class="text-gm-red">*</span></label>
                            <input id="last_name" name="last_name" type="text" required autocomplete="family-name"
                                   value="{{ old('last_name') }}"
                                   class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                            @error('last_name')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="email" class="gm-meta block mb-2">Email <span class="text-gm-red">*</span></label>
                        <input id="email" name="email" type="email" required autocomplete="username"
                               value="{{ old('email') }}"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('email')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="gm-meta block mb-2">Mot de passe <span class="text-gm-red">*</span></label>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('password')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="gm-meta block mb-2">Confirmation <span class="text-gm-red">*</span></label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('password_confirmation')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="gm-btn-primary w-full">Créer mon compte</button>
                </form>
            </div>

            <p class="gm-meta mt-8 text-center normal-case tracking-normal">
                Déjà un compte ?
                <a href="{{ route('login') }}" class="font-bold text-gm-red hover:text-gm-red-bright">
                    Se connecter
                </a>
            </p>
        </div>
    </section>
</x-layouts.public>
