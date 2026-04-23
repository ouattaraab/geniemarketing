<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Corrections de types sur les colonnes 2FA masquées par SQLite (permissif)
 * et révélées en MySQL strict :
 *
 *  - `2fa_secret` : VARCHAR(255) était trop étroit pour le secret chiffré
 *    via Crypt::encryptString (~260–280 caractères de JSON base64). Passage
 *    à TEXT.
 *  - `2fa_recovery_codes` : déclaré JSON mais on y stocke la chaîne
 *    chiffrée (Crypt::encryptString(json_encode($codes))) qui n'est pas
 *    du JSON valide du point de vue MySQL. Passage à TEXT.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('2fa_secret')->nullable()->change();
            $table->text('2fa_recovery_codes')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('2fa_secret', 255)->nullable()->change();
            $table->json('2fa_recovery_codes')->nullable()->change();
        });
    }
};
