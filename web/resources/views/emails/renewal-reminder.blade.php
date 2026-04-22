<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Renouvellement — GÉNIE MARKETING Mag</title>
    <style>
        body { background: #FAF8F4; color: #1A1A1A; font-family: Mulish, Helvetica, Arial, sans-serif; margin: 0; padding: 24px; }
        .wrap { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #E5E2DC; }
        .head { background: #1A1A1A; color: #FAF8F4; padding: 20px 28px; border-bottom: 3px solid #B40F1E; }
        .head h1 { margin: 0; font: italic bold 24px/1 "Zilla Slab", Georgia, serif; }
        .head h1 span { color: #D81B2A; }
        .body { padding: 28px; font-size: 15px; line-height: 1.55; }
        .body h2 { font: italic bold 22px/1.2 "Zilla Slab", Georgia, serif; color: #1A1A1A; margin: 0 0 14px; }
        .badge { display: inline-block; background: #B40F1E; color: #fff; font-size: 11px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; padding: 4px 8px; }
        .cta { display: inline-block; background: #B40F1E; color: #fff !important; text-decoration: none; padding: 12px 24px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; font-size: 12px; margin: 16px 0; }
        .foot { padding: 20px 28px; font-size: 11px; color: #7A7A7A; border-top: 1px solid #E5E2DC; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head"><h1>GÉNIE <span>MARKETING</span></h1></div>
    <div class="body">
        <span class="badge">Renouvellement</span>
        <h2 style="margin-top:14px;">Votre abonnement arrive à échéance</h2>

        <p>Bonjour {{ $user->first_name }},</p>

        <p>
            Votre abonnement <strong>{{ $plan->name }}</strong> expire
            <strong>le {{ $subscription->end_date->locale('fr')->translatedFormat('j F Y') }}</strong>
            ({{ $daysUntilExpiration }} jour{{ $daysUntilExpiration > 1 ? 's' : '' }}).
        </p>

        @if ($subscription->auto_renewal)
            <p>
                Le renouvellement sera <strong>automatique</strong> : vous n'avez rien à faire.
                Vous serez débité de {{ number_format($plan->price_cents / 100, 0, ',', ' ') }} {{ $plan->currency }}
                via votre moyen de paiement habituel.
            </p>
        @else
            <p>
                Le renouvellement automatique est <strong>désactivé</strong>.
                Pour continuer à accéder aux analyses premium sans interruption, renouvelez dès maintenant :
            </p>
            <p style="text-align:center;">
                <a href="{{ url('/abonnement') }}" class="cta">Renouveler mon abonnement</a>
            </p>
        @endif

        <p style="margin-top:24px; color:#4B4B4B;">
            Une question ? Répondez à cet email, nous vous revenons sous 24h ouvrées.
        </p>
    </div>
    <div class="foot">
        GÉNIE MARKETING Mag · Abidjan, Côte d'Ivoire · <a href="{{ url('/compte') }}" style="color:#B40F1E;">Mon espace</a>
    </div>
</div>
</body>
</html>
