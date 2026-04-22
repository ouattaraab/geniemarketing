<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <title>{{ $invoice->number }}</title>
    <style>
        @page { margin: 28mm 22mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1A1A1A;
            font-size: 11px;
            line-height: 1.45;
            margin: 0;
        }
        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #B40F1E;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header-left, .header-right { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { text-align: right; }
        .logo {
            font-family: serif;
            font-style: italic;
            font-weight: bold;
            font-size: 22px;
        }
        .logo .red { color: #B40F1E; }
        .subtitle { font-size: 9px; letter-spacing: 0.2em; text-transform: uppercase; color: #7A7A7A; margin-top: 4px; }

        .invoice-meta {
            font-family: monospace;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #7A7A7A;
        }
        .invoice-meta strong { color: #B40F1E; }

        .parties {
            display: table;
            width: 100%;
            margin: 24px 0;
        }
        .party {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }
        .party-label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #B40F1E;
            margin-bottom: 6px;
            font-weight: bold;
        }
        .party-name { font-weight: bold; font-size: 13px; color: #1A1A1A; }
        .party-block { margin-top: 2px; }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0 10px;
        }
        table.items th {
            background: #1A1A1A;
            color: #fff;
            text-align: left;
            padding: 8px 10px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        table.items th.num { text-align: right; }
        table.items td { padding: 10px; border-bottom: 1px solid #E5E2DC; vertical-align: top; }
        table.items td.num { text-align: right; }

        .totals { margin-top: 12px; width: 60%; margin-left: auto; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 10px; font-size: 11px; }
        .totals tr.total td {
            background: #B40F1E;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
        }

        .footer {
            position: fixed;
            bottom: -16mm;
            left: 0;
            right: 0;
            border-top: 1px solid #E5E2DC;
            padding-top: 10px;
            font-size: 9px;
            color: #7A7A7A;
            text-align: center;
            font-family: monospace;
            letter-spacing: 0.05em;
        }

        .mentions {
            margin-top: 24px;
            padding: 10px 12px;
            border-left: 3px solid #B40F1E;
            background: #F2EFE8;
            font-size: 10px;
            color: #4B4B4B;
        }
    </style>
</head>
<body>
    @php
        $user = $invoice->order->user;
        $snap = $invoice->billing_snapshot ?? [];
        $plan = $invoice->order->plan;
    @endphp

    <div class="header">
        <div class="header-left">
            <div class="logo">GÉNIE <span class="red">MARKETING</span></div>
            <div class="subtitle">Le magazine du marketing ivoirien</div>
        </div>
        <div class="header-right invoice-meta">
            <div><strong>FACTURE</strong></div>
            <div>N° {{ $invoice->number }}</div>
            <div>Émise le {{ $invoice->issued_at->locale('fr')->translatedFormat('j F Y') }}</div>
            <div>Ref. commande · {{ $invoice->order->reference }}</div>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <div class="party-label">Émetteur</div>
            <div class="party-name">{{ $publisher['name'] }}</div>
            <div class="party-block">
                {{ $publisher['address'] }}<br>
                @if (! empty($publisher['email'])) {{ $publisher['email'] }}<br> @endif
                @if (! empty($publisher['phone'])) Tél. {{ $publisher['phone'] }}<br> @endif
                @if (! empty($publisher['siret'])) SIRET : {{ $publisher['siret'] }}<br> @endif
                @if (! empty($publisher['vat_number'])) TVA : {{ $publisher['vat_number'] }} @endif
            </div>
        </div>
        <div class="party">
            <div class="party-label">Client</div>
            <div class="party-name">{{ $snap['name'] ?? $user->fullName() }}</div>
            <div class="party-block">
                {{ $snap['email'] ?? $user->email }}<br>
                @if (! empty($snap['phone'])) {{ $snap['phone'] }}<br> @endif
                @if (! empty($snap['address'])) {{ $snap['address'] }}<br> @endif
            </div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Désignation</th>
                <th class="num">Qté</th>
                <th class="num">PU HT</th>
                <th class="num">Total HT</th>
            </tr>
        </thead>
        <tbody>
            @foreach (($invoice->order->items ?? []) as $item)
                <tr>
                    <td>
                        <strong>{{ $item['plan_name'] ?? 'Abonnement' }}</strong><br>
                        <span style="color:#7A7A7A; font-size:10px;">
                            Formule {{ $item['plan_code'] ?? '' }} · {{ $item['duration_months'] ?? 12 }} mois
                        </span>
                    </td>
                    <td class="num">{{ $item['quantity'] ?? 1 }}</td>
                    <td class="num">{{ number_format(($item['unit_price_cents'] ?? 0) / 100, 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format((($item['unit_price_cents'] ?? 0) * ($item['quantity'] ?? 1)) / 100, 0, ',', ' ') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <table>
            <tr>
                <td>Total HT</td>
                <td class="num">{{ number_format($invoice->amount_ht_cents / 100, 0, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            @if ($invoice->order->discount_cents > 0)
                <tr>
                    <td>Remise</td>
                    <td class="num">− {{ number_format($invoice->order->discount_cents / 100, 0, ',', ' ') }} {{ $invoice->currency }}</td>
                </tr>
            @endif
            <tr>
                <td>TVA ({{ (int) config('gm.tax_rate_percent', 0) }}%)</td>
                <td class="num">{{ number_format($invoice->tax_cents / 100, 0, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
            <tr class="total">
                <td>Total TTC</td>
                <td class="num">{{ number_format($invoice->amount_ttc_cents / 100, 0, ',', ' ') }} {{ $invoice->currency }}</td>
            </tr>
        </table>
    </div>

    <div class="mentions">
        <strong>Mentions légales</strong> — Cette facture est émise par voie électronique conformément aux dispositions en vigueur en Côte d'Ivoire.
        En cas de retard de paiement, des pénalités de 3 fois le taux d'intérêt légal sont applicables.
        Aucun escompte pour paiement anticipé. TVA non applicable, article 293 B du CGI (services numériques — à confirmer avec votre comptable).
    </div>

    <div class="footer">
        Facture {{ $invoice->number }} · Généré le {{ now()->format('d/m/Y H:i') }} · {{ $publisher['name'] }}
    </div>
</body>
</html>
