<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historique des versions d'article (US-020) — une entrée par sauvegarde manuelle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')
                ->constrained('articles')
                ->cascadeOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->unsignedInteger('revision');
            $table->string('title');
            $table->string('lede', 500)->nullable();
            $table->json('body')->nullable();
            $table->text('change_note')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['article_id', 'revision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_versions');
    }
};
