<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Transaction de paiement auprès d'un gateway (paystack, cinetpay…).
 * Une commande peut avoir plusieurs tentatives de paiement.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->string('provider')->index();          // paystack, cinetpay...
            $table->string('provider_reference')->index(); // reference Paystack (GM-xxx ou autogen)
            $table->string('provider_transaction_id')->nullable();

            $table->enum('channel', ['card', 'mobile_money', 'bank', 'qr', 'other'])->default('card');
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'abandoned', 'reversed'])
                ->default('pending')->index();

            $table->unsignedBigInteger('amount_cents');
            $table->unsignedBigInteger('fees_cents')->default(0);
            $table->string('currency', 3)->default('XOF');

            $table->json('raw_response')->nullable();       // payload complet du gateway
            $table->string('failure_reason')->nullable();

            $table->timestamp('authorized_at')->nullable();
            $table->timestamp('captured_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
