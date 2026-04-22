<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>Confirmez votre inscription</title>
    <style>
        body { background: #FAF8F4; color: #1A1A1A; font-family: Mulish, Helvetica, Arial, sans-serif; margin: 0; padding: 24px; }
        .wrap { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #E5E2DC; }
        .head { background: #1A1A1A; color: #FAF8F4; padding: 20px 28px; border-bottom: 3px solid #B40F1E; }
        .head h1 { margin: 0; font: italic bold 24px/1 "Zilla Slab", Georgia, serif; }
        .head h1 span { color: #D81B2A; }
        .body { padding: 28px; font-size: 15px; line-height: 1.55; }
        .body h2 { font: italic bold 20px/1.2 "Zilla Slab", Georgia, serif; color: #1A1A1A; margin: 0 0 12px; }
        .cta { display: inline-block; background: #B40F1E; color: #fff !important; text-decoration: none; padding: 12px 24px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; font-size: 12px; margin: 20px 0; }
        .foot { padding: 20px 28px; font-size: 11px; color: #7A7A7A; border-top: 1px solid #E5E2DC; }
        .foot a { color: #B40F1E; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="head">
        <h1>GÉNIE <span>MARKETING</span></h1>
    </div>
    <div class="body">
        <h2>Confirmez votre inscription à {{ $newsletter->name }}</h2>
        <p>
            Merci pour votre intérêt ! Cliquez sur le bouton ci-dessous pour confirmer
            votre adresse <strong>{{ $subscription->email }}</strong>.
        </p>
        <p style="text-align:center;">
            <a href="{{ $confirmUrl }}" class="cta">Confirmer mon inscription</a>
        </p>
        <p style="color:#7A7A7A; font-size:13px;">
            Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email — aucune inscription ne sera prise en compte.
        </p>
    </div>
    <div class="foot">
        GÉNIE MARKETING Mag · <a href="{{ $unsubscribeUrl }}">Se désabonner</a>
    </div>
</div>
</body>
</html>
