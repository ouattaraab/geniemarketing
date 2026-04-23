<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Articles payants à l'unité : ajoute le prix en centimes. 0 ou NULL = gratuit
 * (le paywall retombe alors sur access_level seul).
 *
 * Combiné à `access_level = 'premium'`, un article peut être proposé à la
 * vente individuelle. L'achat crée une ligne `access_rights` liée à l'Order,
 * donnant à l'utilisateur un accès permanent à cet article (sauf
 * expires_at renseigné par un admin).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->unsignedBigInteger('price_cents')->default(0)->after('access_level');
            $table->string('price_currency', 3)->default('XOF')->after('price_cents');
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn(['price_cents', 'price_currency']);
        });
    }
};
