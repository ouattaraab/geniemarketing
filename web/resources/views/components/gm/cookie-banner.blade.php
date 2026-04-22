{{--
    Bandeau de consentement cookies — conforme CNIL / ARTCI.
    Stockage des choix en localStorage ("gm.consent" = JSON {essential, analytics, marketing, decidedAt}).
    Tant que non décidé, aucun cookie non-essentiel n'est posé.
--}}
<div
    x-data="gmCookieBanner()"
    x-init="init()"
    x-show="show"
    x-cloak
    class="fixed inset-x-0 bottom-0 z-[60] border-t-2 border-gm-red bg-gm-ink text-gm-paper shadow-2xl"
>
    <div class="mx-auto flex max-w-container flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:gap-6">
        <div class="flex-1">
            <h3 class="font-slab text-lg font-bold italic">
                Respect de votre vie privée
            </h3>
            <p class="mt-1 text-sm leading-relaxed text-gm-paper/85">
                Nous utilisons uniquement des cookies essentiels au fonctionnement du site (session, sécurité).
                Avec votre accord, nous pouvons aussi mesurer la fréquentation et améliorer votre expérience.
                Vous pouvez modifier vos choix à tout moment depuis la page
                <a href="/cookies" class="text-gm-red-bright underline">Cookies</a>.
            </p>

            <template x-if="showDetails">
                <div class="mt-4 space-y-3 border-t border-gm-paper/20 pt-4 text-sm">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" checked disabled class="mt-0.5 accent-gm-red" />
                        <span>
                            <strong class="block">Essentiels</strong>
                            <span class="text-gm-paper/70">Session, CSRF, panier d'abonnement. Obligatoires.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="choices.analytics" class="mt-0.5 accent-gm-red" />
                        <span>
                            <strong class="block">Mesure d'audience</strong>
                            <span class="text-gm-paper/70">Comprendre quelles analyses vous intéressent (agrégés, anonymisés).</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" x-model="choices.marketing" class="mt-0.5 accent-gm-red" />
                        <span>
                            <strong class="block">Marketing</strong>
                            <span class="text-gm-paper/70">Personnaliser les offres et la newsletter.</span>
                        </span>
                    </label>
                </div>
            </template>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button
                @click="refuseAll()"
                class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-paper/80 hover:text-white"
            >Refuser tout</button>
            <button
                @click="showDetails = !showDetails"
                class="font-mono text-[11px] font-bold uppercase tracking-[0.15em] text-gm-paper/80 hover:text-white"
            >
                <span x-text="showDetails ? 'Masquer' : 'Personnaliser'"></span>
            </button>
            <button
                @click="acceptCustom()"
                x-show="showDetails"
                class="gm-btn-primary"
            >Valider mes choix</button>
            <button
                @click="acceptAll()"
                x-show="!showDetails"
                class="gm-btn-primary"
            >Tout accepter</button>
        </div>
    </div>
</div>

@once
    @push('head')
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <script>
            window.gmCookieBanner = function () {
                return {
                    show: false,
                    showDetails: false,
                    choices: { essential: true, analytics: false, marketing: false },

                    init() {
                        const raw = localStorage.getItem('gm.consent');
                        if (! raw) {
                            this.show = true;
                            return;
                        }
                        try {
                            const c = JSON.parse(raw);
                            this.choices = { ...this.choices, ...c };
                        } catch (e) {
                            this.show = true;
                        }
                    },

                    acceptAll() {
                        this.choices = { essential: true, analytics: true, marketing: true };
                        this.save();
                    },
                    refuseAll() {
                        this.choices = { essential: true, analytics: false, marketing: false };
                        this.save();
                    },
                    acceptCustom() { this.save(); },

                    save() {
                        const payload = { ...this.choices, decidedAt: new Date().toISOString() };
                        localStorage.setItem('gm.consent', JSON.stringify(payload));
                        this.show = false;
                        window.dispatchEvent(new CustomEvent('gm:consent', { detail: payload }));
                    },
                };
            };
        </script>
    @endpush
@endonce
