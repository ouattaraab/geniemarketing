<section class="border border-gm-red bg-gm-red-soft p-6 sm:p-8" x-data="{ open: {{ $errors->userDeletion->isNotEmpty() ? 'true' : 'false' }} }">
    <header>
        <h3 class="font-slab text-xl font-bold italic text-gm-red-deep">Supprimer mon compte</h3>
        <p class="mt-2 text-sm text-gm-charcoal">
            Cette action est définitive. Toutes vos données personnelles, abonnements et factures
            seront supprimés. Téléchargez au préalable les factures que vous souhaitez conserver.
        </p>
    </header>

    <button type="button" @click="open = true"
            class="mt-4 border border-gm-red bg-white px-4 py-2 font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-red hover:bg-gm-red hover:text-white transition-colors">
        Supprimer définitivement
    </button>

    {{-- Modal confirmation --}}
    <div x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-gm-ink/70 px-6"
         @keydown.escape.window="open = false">
        <div class="w-full max-w-md border border-gm-red bg-white p-6 sm:p-8" @click.outside="open = false">
            <h4 class="font-slab text-xl font-bold italic text-gm-ink">Êtes-vous sûr ?</h4>
            <p class="mt-2 text-sm text-gm-charcoal">
                Confirmez votre mot de passe pour supprimer définitivement votre compte.
            </p>

            <form method="post" action="{{ route('profile.destroy') }}" class="mt-6 space-y-5">
                @csrf
                @method('delete')

                <div>
                    <label for="delete_password" class="gm-meta block mb-2">Mot de passe <span class="text-gm-red">*</span></label>
                    <input id="delete_password" name="password" type="password" required autocomplete="current-password"
                           class="w-full border border-gm-gray-line bg-white px-4 py-3 text-gm-ink focus:border-gm-red focus:outline-none focus:ring-0" />
                    @if ($errors->userDeletion->get('password'))
                        <p class="gm-meta mt-2 text-gm-red">{{ $errors->userDeletion->first('password') }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button type="button" @click="open = false"
                            class="gm-meta normal-case tracking-normal hover:text-gm-red">
                        Annuler
                    </button>
                    <button type="submit"
                            class="bg-gm-red px-5 py-3 font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-white hover:bg-gm-red-deep">
                        Supprimer mon compte
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>
