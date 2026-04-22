<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Dédoublonnage des vues d'articles : une entrée par (article, fingerprint, jour).
 * On n'identifie jamais un visiteur de façon pérenne — le fingerprint mixe IP + UA + session.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')
                ->constrained('articles')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('fingerprint', 64)->index();
            $table->string('referrer')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['article_id', 'fingerprint', 'created_at'], 'article_views_unique_per_session');
            $table->index(['article_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_views');
    }
};
