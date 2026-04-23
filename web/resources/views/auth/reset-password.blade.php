<x-layouts.public title="Réinitialiser le mot de passe — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <div class="mx-auto max-w-md">
            <x-gm.section-heading subtitle="Sécurité">
                Nouveau mot de passe
            </x-gm.section-heading>

            <div class="border border-gm-gray-line bg-white p-6 sm:p-8">
                <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="token" value="{{ $request->route('token') }}" />

                    <div>
                        <label for="email" class="gm-meta block mb-2">Email <span class="text-gm-red">*</span></label>
                        <input id="email" name="email" type="email" required autofocus autocomplete="username"
                               value="{{ old('email', $request->email) }}"
                               class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                        @error('email')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="gm-meta block mb-2">Nouveau mot de passe <span class="text-gm-red">*</span></label>
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

                    <button type="submit" class="gm-btn-primary w-full">Réinitialiser le mot de passe</button>
                </form>
            </div>
        </div>
    </section>
</x-layouts.public>
