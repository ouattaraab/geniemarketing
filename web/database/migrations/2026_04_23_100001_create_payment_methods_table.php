<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Moyens de paiement installés — pilotés depuis l'admin.
 *
 * Permet à un adm/sup de désactiver un provider (Wave, Paystack…) sans
 * toucher au `.env`. Le `PaymentServiceProvider` lit cette table pour
 * sélectionner le gateway actif ; la vue publique /abonnement affiche un
 * message si aucun provider n'est disponible.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();        // wave, paystack, cinetpay, fake
            $table->string('name');                   // "Wave Business"
            $table->text('description')->nullable(); // "Mobile Money CI/Sénégal"
            $table->boolean('is_enabled')->default(false);
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('env_var')->nullable();   // nom de la var .env principale (audit)
            $table->timestamps();

            $table->index(['is_enabled', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
