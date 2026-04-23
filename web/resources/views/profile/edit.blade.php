<x-layouts.public title="Mon profil — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container-narrow px-8 py-16">
        <x-gm.section-heading subtitle="{{ $user->email }}">
            Mon profil
        </x-gm.section-heading>

        @if (session('status') === 'profile-updated')
            <div class="mb-8 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)">
                Vos informations ont été mises à jour.
            </div>
        @elseif (session('status') === 'password-updated')
            <div class="mb-8 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink"
                 x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)">
                Votre mot de passe a été mis à jour.
            </div>
        @endif

        <div class="mb-8">
            <a href="{{ route('account') }}" class="gm-meta normal-case tracking-normal hover:text-gm-red">
                ← Retour à mon compte
            </a>
        </div>

        {{-- Informations personnelles --}}
        @include('profile.partials.update-profile-information-form')

        {{-- Mot de passe --}}
        <div class="mt-12">
            @include('profile.partials.update-password-form')
        </div>

        {{-- Sécurité (2FA) --}}
        <div class="mt-12 border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Double authentification</h3>
            <p class="mt-2 text-sm text-gm-charcoal">
                Ajoutez une couche de sécurité supplémentaire à votre compte avec un code à usage unique.
            </p>
            <a href="{{ route('2fa.setup') }}" class="gm-btn-primary mt-4 inline-block">
                Configurer la 2FA
            </a>
        </div>

        {{-- Mes consentements --}}
        <div class="mt-12 border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Mes consentements</h3>
            <p class="mt-2 text-sm text-gm-charcoal">
                Historique opposable des documents que vous avez acceptés ou refusés. Preuve conservée
                au titre de la <a href="{{ route('legal.privacy') }}" class="text-gm-red underline">loi 2013-450 / RGPD art. 7</a>.
            </p>
            @if ($consents->isEmpty())
                <p class="gm-meta mt-4">Aucun consentement enregistré pour le moment.</p>
            @else
                <div class="mt-4 border border-gm-gray-line">
                    <table class="w-full">
                        <thead class="border-b border-gm-gray-line bg-gm-paper">
                            <tr class="gm-meta text-left">
                                <th class="px-4 py-2">Document</th>
                                <th class="px-4 py-2">Version</th>
                                <th class="px-4 py-2">Action</th>
                                <th class="px-4 py-2">Source</th>
                                <th class="px-4 py-2">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gm-gray-line text-sm">
                            @foreach ($consents as $c)
                                <tr>
                                    <td class="px-4 py-2">{{ $c->documentLabel() }}</td>
                                    <td class="gm-meta px-4 py-2 normal-case tracking-normal">{{ $c->version }}</td>
                                    <td class="px-4 py-2">
                                        @if ($c->action === \App\Models\Consent::ACTION_GRANTED)
                                            <span class="gm-meta inline-block border border-gm-ink bg-gm-ink px-2 py-0.5 text-white">Accepté</span>
                                        @else
                                            <span class="gm-meta inline-block border border-gm-red bg-gm-red-soft px-2 py-0.5 text-gm-red-deep">Retiré</span>
                                        @endif
                                    </td>
                                    <td class="gm-meta px-4 py-2 normal-case tracking-normal">{{ $c->source }}</td>
                                    <td class="gm-meta px-4 py-2 normal-case tracking-normal">
                                        {{ $c->created_at->locale('fr')->translatedFormat('j M Y à H:i') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Mes données (RGPD — portabilité) --}}
        <div class="mt-12 border border-gm-gray-line bg-white p-6 sm:p-8">
            <h3 class="font-slab text-xl font-bold italic text-gm-ink">Mes données personnelles</h3>
            <p class="mt-2 text-sm text-gm-charcoal">
                Téléchargez l'intégralité des données que nous détenons à votre sujet au format JSON
                (compte, abonnements, commandes, factures, consentements, commentaires, newsletter).
                Droit de portabilité — <a href="{{ route('legal.privacy') }}" class="text-gm-red underline">RGPD art. 20 / loi 2013-450 art. 40</a>.
            </p>
            <a href="{{ route('privacy.export') }}" class="gm-btn-primary mt-4 inline-block" target="_blank" rel="noopener">
                Exporter mes données (JSON)
            </a>
            <p class="gm-meta mt-3 normal-case tracking-normal text-gm-gray">
                L'export est limité à 3 téléchargements toutes les 10 minutes pour des raisons de sécurité.
            </p>
        </div>

        {{-- Suppression de compte --}}
        <div class="mt-12">
            @include('profile.partials.delete-user-form')
        </div>
    </section>
</x-layouts.public>
