<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>{{ $campaign->subject }}</title>
    <style>
        body { background: #FAF8F4; color: #1A1A1A; font-family: Mulish, Helvetica, Arial, sans-serif; margin: 0; padding: 24px; }
        .wrap { max-width: 640px; margin: 0 auto; background: #fff; border: 1px solid #E5E2DC; }
        .head { background: #1A1A1A; color: #FAF8F4; padding: 24px 32px; border-bottom: 3px solid #B40F1E; }
        .head h1 { margin: 0; font: italic bold 26px/1 "Zilla Slab", Georgia, serif; }
        .head h1 span { color: #D81B2A; }
        .preheader { display: none; max-height: 0; overflow: hidden; opacity: 0; }
        .body { padding: 32px; font-size: 15px; line-height: 1.6; }
        .body h2 { font: italic bold 22px/1.25 "Zilla Slab", Georgia, serif; color: #1A1A1A; margin: 0 0 14px; }
        .body p { margin: 0 0 16px; }
        .body a { color: #B40F1E; }
        .cta { display: inline-block; background: #B40F1E; color: #fff !important; text-decoration: none; padding: 12px 28px; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; font-size: 12px; margin: 16px 0; }
        .foot { padding: 20px 32px; font-size: 11px; color: #7A7A7A; border-top: 1px solid #E5E2DC; letter-spacing: 0.05em; }
        .foot a { color: #B40F1E; }
    </style>
</head>
<body>
    @if ($campaign->preheader)
        <div class="preheader">{{ $campaign->preheader }}</div>
    @endif

    <div class="wrap">
        <div class="head">
            <h1>GÉNIE <span>MARKETING</span></h1>
        </div>
        <div class="body">
            <h2>{{ $campaign->subject }}</h2>
            {!! \Illuminate\Support\Str::markdown($campaign->content) !!}

            @if ($campaign->cta_label && $campaign->cta_url)
                <p style="text-align:center;">
                    <a href="{{ $campaign->cta_url }}" class="cta">{{ $campaign->cta_label }}</a>
                </p>
            @endif
        </div>
        <div class="foot">
            Vous recevez cet email car vous êtes inscrit à <strong>{{ $campaign->newsletter->name }}</strong>.<br>
            <a href="{{ $unsubscribeUrl }}">Se désabonner en un clic</a> ·
            <a href="{{ url('/') }}">geniemag.ci</a>
        </div>
    </div>
</body>
</html>
