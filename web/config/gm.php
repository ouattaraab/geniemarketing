<?php

declare(strict_types=1);

/**
 * Configuration spécifique GM Mag — centralise les valeurs métier.
 */
return [
    /*
    | Nombre d'articles premium qu'un visiteur (anonyme ou connecté sans abonnement)
    | peut lire gratuitement par mois avant déclenchement du paywall plein.
    */
    'freemium_monthly_limit' => (int) env('GM_FREEMIUM_LIMIT', 3),

    /*
    | Taux de TVA appliqué en Côte d'Ivoire (services numériques).
    | À ajuster selon avis comptable — 0 pour MVP car articles/PDF sont hors TVA.
    */
    'tax_rate_percent' => (float) env('GM_TAX_RATE', 0),

    /*
    | Coordonnées de l'éditeur pour les factures.
    */
    'publisher' => [
        'name' => env('GM_PUBLISHER_NAME', 'GÉNIE MARKETING Mag'),
        'address' => env('GM_PUBLISHER_ADDRESS', 'Abidjan, Côte d\'Ivoire'),
        'siret' => env('GM_PUBLISHER_SIRET', ''),
        'vat_number' => env('GM_PUBLISHER_VAT', ''),
        'email' => env('GM_PUBLISHER_EMAIL', 'contact@geniemag.ci'),
        'phone' => env('GM_PUBLISHER_PHONE', ''),
    ],
];
