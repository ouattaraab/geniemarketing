<?php

declare(strict_types=1);

use App\Models\Setting;
use Database\Seeders\SettingSeeder;

/**
 * Vérifie que les 4 pages légales (CGU, confidentialité, mentions, cookies)
 * répondent en 200 et contiennent les mentions obligatoires prévues par la
 * loi ivoirienne (2013-450 / 2013-451 / 2017-867 / 2016-555) et le RGPD.
 *
 * Toute modification des vues qui ferait disparaître ces mentions cassera
 * ces tests — c'est voulu : on protège l'éditeur contre la régression
 * juridique silencieuse.
 */

beforeEach(function (): void {
    $this->seed(SettingSeeder::class);
});

it('serves /cgu with 200 and mandatory clauses', function (): void {
    $response = $this->get('/cgu');

    $response->assertStatus(200);
    $response->assertSeeText('Article 1');
    $response->assertSeeText('Article 7');
    $response->assertSeeText('droit de rétractation', false);
    $response->assertSeeText('14 jours');
    $response->assertSeeText('tacite reconduction');
    $response->assertSeeText('droit ivoirien', false);
    $response->assertSeeText('tribunaux');
    // Protection PI : doit citer au moins une des deux références légales.
    $response->assertSeeText('2016-555');
});

it('serves /confidentialite with 200 and RGPD mentions', function (): void {
    $response = $this->get('/confidentialite');

    $response->assertStatus(200);
    $response->assertSeeText('Politique de confidentialité');
    $response->assertSeeText('2013-450');
    $response->assertSeeText('RGPD');
    $response->assertSeeText('Responsable du traitement');
    $response->assertSeeText('Délégué à la Protection des Données', false);
    $response->assertSeeText('ARTCI');
    // Les 9 droits utilisateur doivent être listés (a minima 3 clés).
    $response->assertSeeText("Droit d'accès", false);
    $response->assertSeeText("Droit à l'effacement", false);
    $response->assertSeeText('Droit à la portabilité');
    // Sous-traitants
    $response->assertSeeText('Wave Business');
});

it('serves /mentions-legales with 200 and publisher info', function (): void {
    $response = $this->get('/mentions-legales');

    $response->assertStatus(200);
    $response->assertSeeText('Mentions légales');
    // Éditeur
    $response->assertSeeText((string) Setting::get('legal.editor'));
    $response->assertSeeText('RCCM');
    $response->assertSeeText('Directeur de la publication');
    // Hébergeur obligatoire
    $response->assertSeeText('Hébergeur');
    $response->assertSeeText((string) Setting::get('legal.host_name'));
    // PI
    $response->assertSeeText('Propriété intellectuelle');
    $response->assertSeeText('Accord de Bangui');
});

it('serves /cookies with 200 and consent categories', function (): void {
    $response = $this->get('/cookies');

    $response->assertStatus(200);
    $response->assertSeeText('Politique cookies');
    $response->assertSeeText('strictement nécessaires', false);
    $response->assertSeeText('mesure d\'audience', false);
    $response->assertSeeText('consentement');
    $response->assertSeeText('Global Privacy Control');
    // Cookies techniques clés du projet
    $response->assertSeeText('XSRF-TOKEN');
    $response->assertSeeText('gm.consent');
});

it('displays the configurable "last updated" date from settings', function (): void {
    Setting::put('legal.terms_updated_at', '2026-01-15');
    $response = $this->get('/cgu');

    $response->assertStatus(200);
    $response->assertSeeText('15 janvier 2026');
});
