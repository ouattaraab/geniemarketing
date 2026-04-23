<x-layouts.public :title="'Abonnement '.$plan->name.' — GÉNIE MARKETING Mag'">
    <section class="mx-auto max-w-container px-8 py-16">
        <x-gm.section-heading number="19" subtitle="Étape 1 sur 2 · Vos coordonnées">
            Souscrire
        </x-gm.section-heading>

        @if ($errors->any())
            <div class="mb-8 border-l-4 border-gm-red bg-gm-red-soft px-4 py-3 text-sm text-gm-red-deep">
                <strong class="block font-mono uppercase tracking-[0.15em] text-[11px] mb-2">Merci de corriger :</strong>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('checkout.process', $plan) }}" class="grid gap-8 lg:grid-cols-[2fr_1fr]">
            @csrf

            <div class="space-y-6">
                {{-- Compte --}}
                <section class="border border-gm-gray-line bg-white p-6">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">
                        @auth Vos informations @else Créer votre compte @endauth
                    </h3>
                    <p class="gm-meta mt-1 normal-case tracking-normal">
                        @auth
                            Connecté en tant que <strong class="text-gm-ink">{{ $user->email }}</strong>.
                        @else
                            Vous avez déjà un compte ?
                            <a href="{{ route('login') }}?intent=checkout&plan={{ $plan->code }}" class="text-gm-red underline">Se connecter</a>.
                        @endauth
                    </p>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="gm-meta mb-1 block">Prénom <span class="text-gm-red">*</span></label>
                            <input name="first_name" type="text" required maxlength="120"
                                value="{{ old('first_name', $user?->first_name) }}"
                                class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                        <div>
                            <label class="gm-meta mb-1 block">Nom <span class="text-gm-red">*</span></label>
                            <input name="last_name" type="text" required maxlength="120"
                                value="{{ old('last_name', $user?->last_name) }}"
                                class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="gm-meta mb-1 block">Email <span class="text-gm-red">*</span></label>
                            <input name="email" type="email" required maxlength="255"
                                value="{{ old('email', $user?->email) }}"
                                @auth readonly @endauth
                                class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0 @auth bg-gm-paper @endauth" />
                        </div>
                        <div>
                            <label class="gm-meta mb-1 block">Téléphone <span class="text-gm-red">*</span></label>
                            <input name="phone" type="tel" required maxlength="40"
                                value="{{ old('phone', $user?->phone) }}"
                                placeholder="+225 07 00 00 00 00"
                                class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                            <p class="gm-meta mt-1 text-[10px] normal-case tracking-normal">
                                Requis pour Mobile Money et éventuellement la livraison.
                            </p>
                        </div>
                    </div>

                    @guest
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="gm-meta mb-1 block">Mot de passe <span class="text-gm-red">*</span></label>
                                <input name="password" type="password" required minlength="8"
                                    class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                            </div>
                            <div>
                                <label class="gm-meta mb-1 block">Confirmation <span class="text-gm-red">*</span></label>
                                <input name="password_confirmation" type="password" required minlength="8"
                                    class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                            </div>
                        </div>
                    @endguest
                </section>

                {{-- Adresse (obligatoire pour Combo) --}}
                <section class="border border-gm-gray-line bg-white p-6">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">
                        {{ $isCombo ? 'Adresse de livraison' : 'Adresse de facturation' }}
                        @if (! $isCombo) <span class="gm-meta text-gm-gray">(facultative)</span> @endif
                    </h3>
                    @if ($isCombo)
                        <p class="gm-meta mt-1 normal-case tracking-normal">
                            Le magazine papier est expédié à cette adresse à chaque parution.
                        </p>
                    @endif

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="gm-meta mb-1 block">Adresse @if ($isCombo) <span class="text-gm-red">*</span> @endif</label>
                            <input name="address" type="text" maxlength="255" @if ($isCombo) required @endif
                                value="{{ old('address') }}"
                                placeholder="Rue, quartier, immeuble"
                                class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="gm-meta mb-1 block">Ville @if ($isCombo) <span class="text-gm-red">*</span> @endif</label>
                                <input name="city" type="text" maxlength="80" @if ($isCombo) required @endif
                                    value="{{ old('city') }}"
                                    placeholder="Abidjan"
                                    class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                            </div>
                            <div>
                                <label class="gm-meta mb-1 block">Pays @if ($isCombo) <span class="text-gm-red">*</span> @endif</label>
                                <input name="country" type="text" maxlength="80" @if ($isCombo) required @endif
                                    value="{{ old('country', 'Côte d\'Ivoire') }}"
                                    class="w-full border border-gm-gray-line px-3 py-2 font-sans text-sm focus:border-gm-red focus:ring-0" />
                            </div>
                        </div>
                    </div>
                </section>

                {{-- Code promo --}}
                <section class="border border-gm-gray-line bg-white p-6">
                    <h3 class="font-slab text-xl font-bold italic text-gm-ink">Code promotionnel <span class="gm-meta text-gm-gray">(facultatif)</span></h3>
                    <input name="promo_code" type="text" maxlength="50"
                        value="{{ old('promo_code') }}"
                        placeholder="LANCEMENT30"
                        class="mt-3 w-full md:w-80 border border-gm-gray-line px-3 py-2 font-mono text-sm uppercase focus:border-gm-red focus:ring-0" />
                </section>

                {{-- Consentements --}}
                <section class="border border-gm-gray-line bg-white p-6">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input name="accept_terms" type="checkbox" required value="1"
                            @checked(old('accept_terms'))
                            class="mt-1 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                        <span class="text-sm text-gm-charcoal">
                            J'accepte les <a href="{{ route('legal.terms') }}" target="_blank" class="text-gm-red underline">CGU & CGV</a>
                            et la <a href="{{ route('legal.privacy') }}" target="_blank" class="text-gm-red underline">politique de confidentialité</a>.
                            <span class="text-gm-red">*</span>
                        </span>
                    </label>

                    <label class="mt-4 flex items-start gap-3 cursor-pointer">
                        <input name="newsletter_opt_in" type="checkbox" value="1"
                            @checked(old('newsletter_opt_in', true))
                            class="mt-1 border-gm-gray-line text-gm-red focus:ring-gm-red" />
                        <span class="text-sm text-gm-charcoal">
                            Je souhaite recevoir la newsletter hebdomadaire gratuite (désinscription en 1 clic).
                        </span>
                    </label>
                </section>
            </div>

            {{-- Récap plan + CTA --}}
            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                <div class="border border-gm-red bg-white p-6 shadow-gm-red">
                    @if ($plan->is_featured)
                        <span class="inline-block bg-gm-red px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                            Recommandé
                        </span>
                    @endif
                    <h3 class="mt-3 font-slab text-2xl font-bold italic text-gm-ink">{{ $plan->name }}</h3>
                    <p class="text-sm text-gm-charcoal mt-1">{{ $plan->description }}</p>

                    <div class="my-4 border-y border-gm-gray-line py-4">
                        <div class="font-slab text-4xl font-bold text-gm-ink">
                            {{ number_format($plan->price_cents / 100, 0, ',', ' ') }}
                        </div>
                        <div class="gm-meta">{{ $plan->currency }} / an</div>
                        @if ($plan->trial_days > 0)
                            <div class="mt-2 inline-block bg-gm-red-soft border border-gm-red text-gm-red-deep px-2 py-0.5 font-mono text-[11px] uppercase tracking-[0.1em]">
                                {{ $plan->trial_days }} jours d'essai gratuit
                            </div>
                        @endif
                    </div>

                    <ul class="space-y-2 text-sm text-gm-charcoal">
                        @foreach (($plan->features ?? []) as $feature)
                            <li class="flex items-start gap-2">
                                <span class="mt-1 text-gm-red">▸</span>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>

                    <button type="submit" class="gm-btn-primary mt-6 w-full">
                        Payer {{ number_format($plan->price_cents / 100, 0, ',', ' ') }} {{ $plan->currency }}
                    </button>

                    <p class="gm-meta mt-4 text-center text-[10px] normal-case tracking-normal">
                        Vous serez redirigé vers <strong>Wave</strong> pour le paiement sécurisé.<br>
                        Mobile Money Orange · MTN · Moov · Wave
                    </p>
                </div>

                <div class="border border-gm-gray-line bg-gm-paper p-4 text-sm text-gm-charcoal">
                    <strong class="block font-mono text-[10px] uppercase tracking-[0.15em] text-gm-ink">Besoin d'aide ?</strong>
                    <p class="mt-2">Une question avant de vous abonner ? Écrivez-nous à
                        <a href="mailto:contact@geniemag.ci" class="text-gm-red underline">contact@geniemag.ci</a>.
                    </p>
                </div>
            </aside>
        </form>
    </section>
</x-layouts.public>
