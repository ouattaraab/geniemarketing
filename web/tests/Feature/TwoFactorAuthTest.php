<?php

declare(strict_types=1);

use App\Models\User;
use App\Services\TwoFactorAuth;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    $this->seed(RoleSeeder::class);
    $this->service = app(TwoFactorAuth::class);
});

it('génère un secret TOTP chiffré en BD', function (): void {
    $user = User::factory()->backoffice()->create();

    $secret = $this->service->generateSecret($user);

    expect($secret)->toBeString()->toMatch('/^[A-Z2-7]{16,}$/'); // base32
    expect($user->fresh()->{'2fa_secret'})->not->toBeNull();
    // Le secret stocké est chiffré, donc ≠ du secret brut
    expect($user->fresh()->{'2fa_secret'})->not->toBe($secret);
});

it('refuse un code TOTP invalide lors de l\'activation', function (): void {
    $user = User::factory()->backoffice()->create();
    $this->service->generateSecret($user);

    $result = $this->service->enable($user->fresh(), '000000');

    expect($result)->toBeFalse();
    expect($user->fresh()->{'2fa_enabled'})->toBeFalse();
});

it('active la 2FA avec un code TOTP valide et génère 8 codes de récupération', function (): void {
    $user = User::factory()->backoffice()->create();
    $secret = $this->service->generateSecret($user);

    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $validCode = $google2fa->getCurrentOtp($secret);

    $ok = $this->service->enable($user->fresh(), $validCode);
    expect($ok)->toBeTrue();

    $user->refresh();
    expect($user->{'2fa_enabled'})->toBeTrue();
    expect($user->{'2fa_confirmed_at'})->not->toBeNull();

    $codes = $this->service->recoveryCodes($user);
    expect($codes)->toHaveCount(8);
    foreach ($codes as $code) {
        expect($code)->toMatch('/^[A-Z0-9]{4}-[A-Z0-9]{4}$/');
    }
});

it('consomme un code de récupération à usage unique', function (): void {
    $user = User::factory()->backoffice()->create();
    $secret = $this->service->generateSecret($user);
    $this->service->enable($user->fresh(), app(\PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret));

    $user->refresh();
    $firstCode = $this->service->recoveryCodes($user)[0];

    // Première utilisation : OK
    expect($this->service->verify($user, $firstCode))->toBeTrue();

    // Deuxième tentative avec le même code : refusée
    expect($this->service->verify($user->fresh(), $firstCode))->toBeFalse();

    // Il reste 7 codes
    expect($this->service->recoveryCodes($user->fresh()))->toHaveCount(7);
});

it('désactive la 2FA et efface les codes', function (): void {
    $user = User::factory()->backoffice()->create();
    $secret = $this->service->generateSecret($user);
    $this->service->enable($user->fresh(), app(\PragmaRX\Google2FA\Google2FA::class)->getCurrentOtp($secret));

    $this->service->disable($user->fresh());

    $fresh = $user->fresh();
    expect($fresh->{'2fa_enabled'})->toBeFalse();
    expect($fresh->{'2fa_secret'})->toBeNull();
    expect($fresh->{'2fa_recovery_codes'})->toBeNull();
});
