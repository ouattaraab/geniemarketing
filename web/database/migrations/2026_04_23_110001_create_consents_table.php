<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Traçabilité opposable des consentements RGPD / loi 2013-450.
 *
 * Chaque acceptation d'un document légal (CGU, politique de confidentialité,
 * consentement marketing) est horodatée avec l'IP + user agent + version du
 * document. Permet de prouver le consentement en cas de litige ARTCI/CNIL.
 *
 * Table append-only : on n'UPDATE ni ne DELETE jamais une ligne — un retrait
 * de consentement crée une nouvelle ligne `revoked_at` renseigné.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()              // un invité peut consentir (bandeau cookies)
                ->constrained('users')
                ->nullOnDelete();          // si le user est supprimé, on garde la preuve anonymisée

            $table->string('document', 40)->index();   // terms, privacy, cookies, marketing
            $table->string('version', 40);             // date YYYY-MM-DD de la version acceptée
            $table->string('action', 20)->default('granted'); // granted | revoked

            $table->string('source', 40)->default('checkout'); // checkout, profile, cookie_banner, admin

            // Preuve technique
            $table->string('ip', 45)->nullable();      // IPv4 ou IPv6
            $table->string('user_agent', 500)->nullable();

            $table->timestamps();

            $table->index(['user_id', 'document', 'action']);
            $table->index(['document', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
