<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Formules d'abonnement (EP-07) : Digital, Combo, Entreprise.
 * Prix stockés en centimes (kobos) pour éviter les erreurs d'arrondi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();            // digital, combo, entreprise
            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedBigInteger('price_cents');    // 24 000 FCFA = 2 400 000 centimes
            $table->string('currency', 3)->default('XOF');
            $table->unsignedSmallInteger('duration_months')->default(12);
            $table->unsignedSmallInteger('trial_days')->default(0);

            $table->unsignedSmallInteger('licenses_included')->default(1); // 10 pour Entreprise
            $table->json('features')->nullable();         // liste des avantages

            $table->string('paystack_plan_code')->nullable();  // plan Paystack si abonnement récurrent

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
