<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Preuve opposable de consentement (RGPD art. 7.1 / loi 2013-450 art. 8).
 * Append-only : un retrait crée une nouvelle ligne `action=revoked`.
 */
class Consent extends Model
{
    use HasFactory;

    public const DOC_TERMS = 'terms';
    public const DOC_PRIVACY = 'privacy';
    public const DOC_COOKIES = 'cookies';
    public const DOC_MARKETING = 'marketing';

    public const ACTION_GRANTED = 'granted';
    public const ACTION_REVOKED = 'revoked';

    protected $fillable = [
        'user_id',
        'document',
        'version',
        'action',
        'source',
        'ip',
        'user_agent',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Enregistre un consentement. Horodatage immuable, traçabilité IP/UA.
     */
    public static function record(
        ?int $userId,
        string $document,
        string $version,
        string $action = self::ACTION_GRANTED,
        string $source = 'checkout',
        ?string $ip = null,
        ?string $userAgent = null,
    ): self {
        return static::create([
            'user_id' => $userId,
            'document' => $document,
            'version' => $version,
            'action' => $action,
            'source' => $source,
            'ip' => $ip,
            'user_agent' => $userAgent ? mb_substr($userAgent, 0, 500) : null,
        ]);
    }

    /**
     * Libellé humain d'un document.
     */
    public function documentLabel(): string
    {
        return match ($this->document) {
            self::DOC_TERMS => 'Conditions générales (CGU/CGV)',
            self::DOC_PRIVACY => 'Politique de confidentialité',
            self::DOC_COOKIES => 'Politique cookies',
            self::DOC_MARKETING => 'Consentement marketing / newsletter',
            default => ucfirst($this->document),
        };
    }
}
