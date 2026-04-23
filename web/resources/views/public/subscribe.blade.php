<x-layouts.public title="Abonnement — GÉNIE MARKETING Mag">
    <section class="mx-auto max-w-container px-8 py-16">
        <x-gm.section-heading subtitle="Choisissez votre formule">
            S'abonner
        </x-gm.section-heading>

        @if ($errors->has('payment'))
            <div class="mb-8 border-l-4 border-gm-red bg-gm-red-soft px-4 py-3 text-sm text-gm-red-deep">
                {{ $errors->first('payment') }}
            </div>
        @endif

        @if (session('status'))
            <div class="mb-8 border-l-4 border-gm-ink bg-white px-4 py-3 text-sm text-gm-ink">
                {{ session('status') }}
            </div>
        @endif

        @if (! ($paymentEnabled ?? true))
            <div class="mb-8 border-l-4 border-gm-red bg-gm-red-soft px-4 py-4 text-sm text-gm-red-deep">
                <strong>Les abonnements sont temporairement indisponibles.</strong>
                Aucun moyen de paiement n'est actif pour le moment. Merci de réessayer
                plus tard ou écrivez-nous à
                <a href="mailto:contact@geniemag.ci" class="underline">contact@geniemag.ci</a>.
            </div>
        @endif

        @if ($plans->isEmpty())
            <div class="mb-8 border border-dashed border-gm-gray-line bg-white px-6 py-10 text-center">
                <p class="gm-meta">Aucune formule disponible pour le moment.</p>
                <p class="mt-3 font-slab text-lg italic text-gm-charcoal">
                    Nos formules d'abonnement sont en cours de préparation. Revenez très bientôt.
                </p>
            </div>
        @else
            <div class="grid gap-6 md:grid-cols-3">
                @foreach ($plans as $plan)
                    @php $featured = (bool) $plan->is_featured; @endphp
                    <div class="flex flex-col border {{ $featured ? 'border-gm-red shadow-gm-red bg-white' : 'border-gm-gray-line bg-white' }} p-8">
                        @if ($featured)
                            <span class="mb-4 inline-block self-start bg-gm-red px-2 py-1 font-mono text-[10px] font-bold uppercase tracking-[0.15em] text-white">
                                Recommandé
                            </span>
                        @endif
                        <h3 class="font-slab text-2xl font-bold italic text-gm-ink">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-gm-charcoal">{{ $plan->description }}</p>

                        <div class="my-6">
                            <span class="font-slab text-4xl font-bold text-gm-ink">
                                {{ number_format($plan->price_cents / 100, 0, ',', ' ') }}
                            </span>
                            <span class="gm-meta ml-2">{{ $plan->currency }} / an</span>
                            @if ($plan->trial_days > 0)
                                <div class="gm-meta mt-2 text-gm-red">
                                    {{ $plan->trial_days }} jours d'essai gratuit
                                </div>
                            @endif
                        </div>

                        <ul class="mb-6 space-y-2 text-sm text-gm-charcoal">
                            @foreach (($plan->features ?? []) as $feature)
                                <li class="flex items-start gap-2">
                                    <span class="mt-1 text-gm-red">▸</span>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        @if ($paymentEnabled ?? true)
                            <a href="{{ route('checkout.form', $plan) }}" class="gm-btn-primary mt-auto w-full text-center block">
                                Choisir cette formule
                            </a>
                        @else
                            <button type="button" disabled
                                    class="gm-btn-primary mt-auto w-full text-center block opacity-50 cursor-not-allowed">
                                Indisponible
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        <p class="gm-meta mt-10 text-center">
            Paiement sécurisé par Wave · Mobile Money Orange / MTN / Moov · Compatible tous opérateurs
        </p>
    </section>
</x-layouts.public>
