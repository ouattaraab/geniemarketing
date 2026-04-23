<section class="border border-gm-gray-line bg-white p-6 sm:p-8">
    <header>
        <h3 class="font-slab text-xl font-bold italic text-gm-ink">Informations personnelles</h3>
        <p class="mt-2 text-sm text-gm-charcoal">
            Mettez à jour vos coordonnées et votre adresse email.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="first_name" class="gm-meta block mb-2">Prénom <span class="text-gm-red">*</span></label>
                <input id="first_name" name="first_name" type="text" required autofocus autocomplete="given-name"
                       value="{{ old('first_name', $user->first_name) }}"
                       class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                @error('first_name')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="last_name" class="gm-meta block mb-2">Nom <span class="text-gm-red">*</span></label>
                <input id="last_name" name="last_name" type="text" required autocomplete="family-name"
                       value="{{ old('last_name', $user->last_name) }}"
                       class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                @error('last_name')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="email" class="gm-meta block mb-2">Email <span class="text-gm-red">*</span></label>
            <input id="email" name="email" type="email" required autocomplete="username"
                   value="{{ old('email', $user->email) }}"
                   class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
            @error('email')<p class="gm-meta mt-2 text-gm-red">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <p class="mt-3 text-sm text-gm-charcoal">
                    Votre email n'est pas encore vérifié.
                    <button form="send-verification" class="underline hover:text-gm-red">
                        Renvoyer l'email de vérification
                    </button>
                </p>
                @if (session('status') === 'verification-link-sent')
                    <p class="gm-meta mt-2 text-gm-ink">Lien de vérification envoyé.</p>
                @endif
            @endif
        </div>

        <button type="submit" class="gm-btn-primary">Enregistrer</button>
    </form>
</section>
