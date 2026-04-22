<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Facture générée après paiement — numérotation séquentielle annuelle.
 * Conformité CI : TVA 18%, mentions légales, rétention 10 ans.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();
            $table->foreignId('subscription_id')
                ->nullable()
                ->constrained('subscriptions')
                ->nullOnDelete();

            $table->string('number')->unique();             // GM-FAC-2026-000001
            $table->string('pdf_path')->nullable();         // Storage path
            $table->string('pdf_disk')->nullable();

            $table->unsignedBigInteger('amount_ht_cents');
            $table->unsignedBigInteger('tax_cents');
            $table->unsignedBigInteger('amount_ttc_cents');
            $table->string('currency', 3)->default('XOF');

            $table->json('billing_snapshot')->nullable();   // nom, adresse, SIRET… au moment de l'émission

            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index('issued_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
