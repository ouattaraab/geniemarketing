<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pivot article ↔ auteur, avec co-signature ordonnée (position) et rôle optionnel.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_author', function (Blueprint $table) {
            $table->foreignId('article_id')
                ->constrained('articles')
                ->cascadeOnDelete();
            $table->foreignId('author_id')
                ->constrained('authors')
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('position')->default(0);
            $table->string('role')->nullable();

            $table->primary(['article_id', 'author_id']);
            $table->index('author_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_author');
    }
};
