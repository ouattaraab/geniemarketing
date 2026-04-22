<div>
    <div class="mb-6">
        <a href="{{ route('admin.users.index') }}" class="gm-meta hover:text-gm-red">← Retour à la liste</a>
        <h2 class="mt-2 font-slab text-3xl font-bold italic text-gm-ink">
            {{ $user?->exists ? 'Édition · '.$firstName.' '.$lastName : 'Nouveau collaborateur' }}
        </h2>
    </div>

    <form wire:submit="save" class="max-w-2xl space-y-6 border border-gm-gray-line bg-white p-6">
        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="gm-meta mb-2 block">Prénom</label>
                <input type="text" wire:model="firstName" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                @error('firstName')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="gm-meta mb-2 block">Nom</label>
                <input type="text" wire:model="lastName" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                @error('lastName')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="gm-meta mb-2 block">Email professionnel</label>
            <input type="email" wire:model="email" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
            @error('email')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="gm-meta mb-2 block">Téléphone</label>
            <input type="tel" wire:model="phone" placeholder="+225 07 00 00 00 00" class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
        </div>

        <div class="grid gap-6 md:grid-cols-2">
            <div>
                <label class="gm-meta mb-2 block">Rôle</label>
                <select wire:model="role" class="w-full border border-gm-gray-line bg-white px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="red">Rédacteur</option>
                    <option value="chef">Rédacteur en chef</option>
                    <option value="edit">Administrateur éditorial</option>
                    <option value="com">Gestionnaire commercial</option>
                    <option value="adm">Administrateur système</option>
                    <option value="sup">Super administrateur</option>
                </select>
                @error('role')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="gm-meta mb-2 block">Statut</label>
                <select wire:model="status" class="w-full border border-gm-gray-line bg-white px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0">
                    <option value="active">Actif</option>
                    <option value="inactive">Inactif</option>
                    <option value="pending">En attente d'activation</option>
                </select>
                @error('status')<p class="gm-meta mt-1 text-gm-red">{{ $message }}</p>@enderror
            </div>
        </div>

        @if (! $user?->exists)
            <div class="border border-gm-red-soft bg-gm-red-soft/50 p-4">
                <p class="gm-meta text-gm-red-deep">
                    À la création, un mot de passe provisoire est généré et un lien d'activation est envoyé à l'adresse email.
                    L'utilisateur définit son mot de passe au premier accès.
                </p>
            </div>
        @endif

        <div class="flex items-center justify-end gap-3 border-t border-gm-gray-line pt-6">
            <a href="{{ route('admin.users.index') }}" class="gm-meta hover:text-gm-red">Annuler</a>
            <button type="submit" wire:loading.attr="disabled" class="gm-btn-primary">
                <span wire:loading.remove wire:target="save">Enregistrer</span>
                <span wire:loading wire:target="save">Enregistrement…</span>
            </button>
        </div>
    </form>
</div>
