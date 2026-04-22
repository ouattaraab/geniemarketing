{{-- Simulateur local de hosted checkout — stand-in Paystack en dev --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Simulateur de paiement — GM Mag (DEV)</title>
    @vite(['resources/css/app.css'])
    <style>
        body { background: #0f172a; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: system-ui, sans-serif; }
    </style>
</head>
<body>
    <div class="w-full max-w-md px-6">
        <div class="mb-4 rounded border-2 border-amber-400 bg-amber-50 p-3 text-center text-xs font-bold uppercase tracking-wider text-amber-900">
            ⚠ Mode DEV — simulateur local (pas un vrai paiement)
        </div>

        <div class="overflow-hidden rounded-lg bg-white shadow-2xl">
            <div class="bg-[#00C3F7] px-6 py-4">
                <div class="flex items-center gap-2 text-white">
                    <span class="font-bold text-lg">Paystack</span>
                    <span class="text-xs opacity-75">(simulé)</span>
                </div>
            </div>

            <div class="p-6">
                <div class="mb-4 border-b border-slate-200 pb-4">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Commande</div>
                    <div class="mt-1 font-mono text-sm text-slate-900">{{ $reference }}</div>
                </div>

                <div class="mb-6">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Montant à payer</div>
                    <div class="mt-1 text-3xl font-bold text-slate-900">
                        {{ number_format($order->total_cents / 100, 0, ',', ' ') }}
                        <span class="text-lg font-normal text-slate-600">{{ $order->currency }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <form method="POST" action="{{ route('checkout.simulator.submit', $reference) }}">
                        @csrf
                        <input type="hidden" name="callback" value="{{ $callback }}" />
                        <input type="hidden" name="outcome" value="success" />
                        <button type="submit"
                                class="w-full rounded bg-emerald-600 px-4 py-3 font-bold text-white transition hover:bg-emerald-700">
                            Payer {{ number_format($order->total_cents / 100, 0, ',', ' ') }} {{ $order->currency }}
                        </button>
                    </form>

                    <form method="POST" action="{{ route('checkout.simulator.submit', $reference) }}">
                        @csrf
                        <input type="hidden" name="callback" value="{{ $callback }}" />
                        <input type="hidden" name="outcome" value="failed" />
                        <button type="submit"
                                class="w-full rounded border border-rose-300 bg-white px-4 py-2 text-sm text-rose-700 transition hover:bg-rose-50">
                            Simuler un échec (carte refusée)
                        </button>
                    </form>

                    <form method="POST" action="{{ route('checkout.simulator.submit', $reference) }}">
                        @csrf
                        <input type="hidden" name="callback" value="{{ $callback }}" />
                        <input type="hidden" name="outcome" value="abandoned" />
                        <button type="submit"
                                class="w-full rounded border border-slate-300 bg-white px-4 py-2 text-sm text-slate-600 transition hover:bg-slate-50">
                            Annuler / Abandonner
                        </button>
                    </form>
                </div>

                <p class="mt-6 text-center text-xs text-slate-400">
                    Ce simulateur remplace Paystack tant que la clé API est un placeholder.
                    Aucune transaction réelle n'est effectuée.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
