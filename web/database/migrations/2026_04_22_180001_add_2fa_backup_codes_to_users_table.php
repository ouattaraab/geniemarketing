<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Complément 2FA TOTP (US-006) : codes de récupération chiffrés + horodatage confirmation.
 * Les colonnes `2fa_enabled` et `2fa_secret` existent déjà depuis la migration initiale users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('2fa_recovery_codes')->nullable()->after('2fa_secret');
            $table->timestamp('2fa_confirmed_at')->nullable()->after('2fa_recovery_codes');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['2fa_recovery_codes', '2fa_confirmed_at']);
        });
    }
};
