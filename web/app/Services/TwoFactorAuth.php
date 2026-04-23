<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/**
 * Gère la 2FA TOTP pour les utilisateurs backoffice (US-006, P1).
 * - Secret chiffré en BD via Crypt.
 * - 8 codes de récupération à usage unique, chiffrés en JSON.
 */
class TwoFactorAuth
{
    public function __construct(private readonly Google2FA $google2fa) {}

    public function generateSecret(User $user): string
    {
        $secret = $this->google2fa->generateSecretKey();
        $user->{'2fa_secret'} = Crypt::encryptString($secret);
        $user->save();

        return $secret;
    }

    public function enable(User $user, string $code): bool
    {
        if (! $user->{'2fa_secret'}) {
            return false;
        }

        $secret = Crypt::decryptString($user->{'2fa_secret'});
        if (! $this->google2fa->verifyKey($secret, $code)) {
            return false;
        }

        $codes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(4).'-'.Str::random(4)))
            ->toArray();

        $user->{'2fa_enabled'} = true;
        $user->{'2fa_confirmed_at'} = now();
        $user->{'2fa_recovery_codes'} = Crypt::encryptString(json_encode($codes));
        $user->save();

        return true;
    }

    public function disable(User $user): void
    {
        $user->{'2fa_enabled'} = false;
        $user->{'2fa_secret'} = null;
        $user->{'2fa_recovery_codes'} = null;
        $user->{'2fa_confirmed_at'} = null;
        $user->save();
    }

    public function verify(User $user, string $code): bool
    {
        if (! $user->{'2fa_enabled'} || ! $user->{'2fa_secret'}) {
            return false;
        }

        $secret = Crypt::decryptString($user->{'2fa_secret'});

        // 1. TOTP classique
        if ($this->google2fa->verifyKey($secret, $code)) {
            return true;
        }

        // 2. Code de récupération (à usage unique)
        return $this->consumeRecoveryCode($user, $code);
    }

    /**
     * @return array<int, string>
     */
    public function recoveryCodes(User $user): array
    {
        if (! $user->{'2fa_recovery_codes'}) {
            return [];
        }

        return (array) json_decode(Crypt::decryptString($user->{'2fa_recovery_codes'}), true);
    }

    public function qrCodeSvg(User $user, string $secret): string
    {
        $issuer = config('app.name', 'GM Mag');
        $label = rawurlencode($issuer).':'.rawurlencode($user->email);
        $uri = sprintf(
            'otpauth://totp/%s?secret=%s&issuer=%s&algorithm=SHA1&digits=6&period=30',
            $label,
            $secret,
            rawurlencode($issuer),
        );

        $renderer = new ImageRenderer(new RendererStyle(256), new SvgImageBackEnd);

        return (new Writer($renderer))->writeString($uri);
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $this->recoveryCodes($user);
        $normalised = Str::upper(trim($code));

        $idx = array_search($normalised, $codes, true);
        if ($idx === false) {
            return false;
        }

        unset($codes[$idx]);
        $remaining = array_values($codes);

        $user->{'2fa_recovery_codes'} = Crypt::encryptString(json_encode($remaining));
        $user->save();

        return true;
    }
}
