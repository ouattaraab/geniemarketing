<section class="border border-gm-gray-line bg-white p-6 sm:p-8">
    <header>
        <h3 class="font-slab text-xl font-bold italic text-gm-ink">Mot de passe</h3>
        <p class="mt-2 text-sm text-gm-charcoal">
            Utilisez un mot de passe long et unique pour sécuriser votre compte.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="gm-meta block mb-2">Mot de passe actuel <span class="text-gm-red">*</span></label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
            @if ($errors->updatePassword->get('current_password'))
                <p class="gm-meta mt-2 text-gm-red">{{ $errors->updatePassword->first('current_password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password" class="gm-meta block mb-2">Nouveau mot de passe <span class="text-gm-red">*</span></label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                   class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
            @if ($errors->updatePassword->get('password'))
                <p class="gm-meta mt-2 text-gm-red">{{ $errors->updatePassword->first('password') }}</p>
            @endif
        </div>

        <div>
            <label for="update_password_password_confirmation" class="gm-meta block mb-2">Confirmation <span class="text-gm-red">*</span></label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                   class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
            @if ($errors->updatePassword->get('password_confirmation'))
                <p class="gm-meta mt-2 text-gm-red">{{ $errors->updatePassword->first('password_confirmation') }}</p>
            @endif
        </div>

        <button type="submit" class="gm-btn-primary">Enregistrer</button>
    </form>
</section>
