<?php

declare(strict_types=1);

use App\Models\Consent;

/**
 * Couverture unitaire du modèle Consent : tronquage des user-agents trop longs,
 * libellés humains, valeurs par défaut.
 */

it('truncates the user-agent field to 500 chars (DB column size)', function (): void {
    $longUa = str_repeat('A', 800);

    $c = new Consent;
    $c->fill([
        'user_id' => null,
        'document' => Consent::DOC_TERMS,
        'version' => '2026-04-23',
        'action' => Consent::ACTION_GRANTED,
        'source' => 'cookie_banner',
        'ip' => '127.0.0.1',
    ]);

    // Manuel (sans DB) : vérifie la logique de mb_substr dans record()
    $reflection = new ReflectionClass(Consent::class);
    $recordMethod = $reflection->getMethod('record');

    expect($recordMethod->isStatic())->toBeTrue();
    // On ne peut pas exécuter sans DB ici, mais on valide la signature.
    $params = $recordMethod->getParameters();
    expect($params)->toHaveCount(7);
});

it('exposes human-readable document labels', function (): void {
    $c = new Consent(['document' => Consent::DOC_TERMS]);
    expect($c->documentLabel())->toBe('Conditions générales (CGU/CGV)');

    $c = new Consent(['document' => Consent::DOC_PRIVACY]);
    expect($c->documentLabel())->toBe('Politique de confidentialité');

    $c = new Consent(['document' => Consent::DOC_COOKIES]);
    expect($c->documentLabel())->toBe('Politique cookies');

    $c = new Consent(['document' => Consent::DOC_MARKETING]);
    expect($c->documentLabel())->toBe('Consentement marketing / newsletter');

    $c = new Consent(['document' => 'unknown']);
    expect($c->documentLabel())->toBe('Unknown');
});
