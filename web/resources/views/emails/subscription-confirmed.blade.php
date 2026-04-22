<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Abonnement confirmé — GÉNIE MARKETING Mag</title>
    <style>
        body { background: #FAF8F4; color: #1A1A1A; font-family: Mulish, Helvetica, Arial, sans-serif; margin: 0; padding: 24px; }
        .wrap { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #E5E2DC; }
        .head { background: #1A1A1A; color: #FAF8F4; padding: 24px 32px; border-bottom: 3px solid #B40F1E; }
        .head h1 { margin: 0; font: italic bold 28px/1 "Zilla Slab", Georgia, serif; }
        .head h1 span { color: #D81B2A; }
        .body { padding: 32px; font-size: 15px; line-height: 1.55; }
        .body h2 { font: italic bold 22px/1.2 "Zilla Slab", Georgia, serif; color: #1A1A1A; margin: 0 0 16px; }
        .badge { display: inline-block; background: #B40F1E; color: #fff; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; padding: 4px 8px; }
        .card { background: #F2EFE8; border-left: 3px solid #B40F1E; padding: 16px 20px; margin: 24px 0; }
        .card dt { font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #7A7A7A; margin-top: 8px; }
        .card dd { margin: 2px 0 0; font-weight: 600; color: #1A1A1A; }
        .cta { display: inline-block; background: #B40F1E; color: #fff !important; text-decoration: none; padding: 12px 24px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; font-size: 12px; margin-top: 16px; }
        .foot { padding: 24px 32px; font-size: 12px; color: #7A7A7A; border-top: 1px solid #E5E2DC; }
        .foot a { color: #B40F1E; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <h1>GÉNIE <span>MARKETING</span></h1>
    </div>

    <div class="body">
        <span class="badge">Abonnement confirmé</span>
        <h2 style="margin-top:16px;">Bienvenue, {{ $user->first_name }} !</h2>

        <p>
            Votre paiement a bien été reçu et votre abonnement
            <strong>{{ $plan->name }}</strong> est désormais actif.
            Vous avez accès à tous les articles premium, aux numéros PDF et à notre newsletter réservée aux abonnés.
        </p>

        <div class="card">
            <dl>
                <dt>Formule</dt>
                <dd>{{ $plan->name }}</dd>
                <dt>Période</dt>
                <dd>
                    Du {{ $subscription->start_date->locale('fr')->translatedFormat('j F Y') }}
                    au {{ $subscription->end_date->locale('fr')->translatedFormat('j F Y') }}
                </dd>
                @if ($subscription->trial_ends_at && $subscription->trial_ends_at->isFuture())
                    <dt>Période d'essai</dt>
                    <dd>Jusqu'au {{ $subscription->trial_ends_at->locale('fr')->translatedFormat('j F Y') }}</dd>
                @endif
                <dt>Montant</dt>
                <dd>{{ number_format($plan->price_cents / 100, 0, ',', ' ') }} {{ $plan->currency }} / an</dd>
                @if ($invoice)
                    <dt>Facture</dt>
                    <dd>{{ $invoice->number }}</dd>
                @endif
            </dl>
        </div>

        <p>
            <a href="{{ url('/compte') }}" class="cta">Accéder à mon espace</a>
        </p>

        <p style="margin-top:32px; color:#4B4B4B;">
            Un doute, une question ? Répondez à cet email — notre équipe vous répondra sous 24 h ouvrées.
        </p>
    </div>

    <div class="foot">
        GÉNIE MARKETING Mag · Abidjan, Côte d'Ivoire · <a href="{{ url('/') }}">geniemag.ci</a><br>
        Pour vous désabonner ou modifier vos préférences, rendez-vous dans <a href="{{ url('/compte') }}">votre espace</a>.
    </div>
</div>
</body>
</html>
