<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commande (abonnement, numéro magazine, article à l'unité).
 * Total en centimes, devise, statut, items JSON pour flexibilité.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();         // GM-2026-000001
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('subscription_plan_id')
                ->nullable()
                ->constrained('subscription_plans')
                ->nullOnDelete();
            $table->foreignId('promo_code_id')
                ->nullable()
                ->constrained('promo_codes')
                ->nullOnDelete();

            $table->enum('type', ['subscription', 'issue', 'article'])->default('subscription');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded', 'cancelled'])
                ->default('pending')->index();

            $table->unsignedBigInteger('subtotal_cents');
            $table->unsignedBigInteger('discount_cents')->default(0);
            $table->unsignedBigInteger('tax_cents')->default(0);         // TVA 18% Côte d'Ivoire
            $table->unsignedBigInteger('total_cents');
            $table->string('currency', 3)->default('XOF');

            $table->json('items')->nullable();               // snapshot des items à la commande
            $table->json('billing_address')->nullable();     // {nom, adresse, ville, pays, téléphone}
            $table->json('shipping_address')->nullable();    // pour Combo papier
            $table->text('notes')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('paid_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
