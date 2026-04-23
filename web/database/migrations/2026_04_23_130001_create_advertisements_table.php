<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bannières publicitaires affichées sur les pages article (et potentiellement
 * ailleurs). Pilotées depuis /admin/bannieres — rôles com/adm/sup.
 *
 * Un placement = un emplacement (article_top, article_bottom, sidebar…).
 * Plusieurs bannières peuvent partager un placement : rotation aléatoire
 * pondérée par `priority` (poids). Programmation via starts_at/ends_at.
 *
 * Compteurs `impressions` et `clicks` incrémentés de manière non bloquante
 * (raw update SQL) pour ne pas pénaliser le temps de réponse.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advertisements', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 200);                // usage interne
            $table->enum('placement', [
                'article_top',
                'article_bottom',
                'article_sidebar',
                'home_leaderboard',
                'home_sidebar',
            ])->index();
            $table->foreignId('media_id')                 // image de la bannière
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->string('image_url', 500)->nullable(); // fallback si pas de media
            $table->string('alt_text', 200)->nullable();
            $table->string('link_url', 1000);
            $table->boolean('link_nofollow')->default(true);
            $table->boolean('link_new_tab')->default(true);
            $table->unsignedSmallInteger('priority')->default(10); // poids rotation (0-100)
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('sponsor_name', 200)->nullable();  // affiché avec "Sponsorisé par…"
            $table->unsignedBigInteger('impressions')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'placement', 'starts_at', 'ends_at'], 'ads_active_window_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
