<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entrée du sommaire d'un numéro (EP-06) — lien optionnel vers l'article numérique.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_summary_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magazine_issue_id')
                ->constrained('magazine_issues')
                ->cascadeOnDelete();
            $table->foreignId('article_id')
                ->nullable()
                ->constrained('articles')
                ->nullOnDelete();

            $table->unsignedSmallInteger('position')->default(0);
            $table->unsignedSmallInteger('page')->nullable();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->string('section')->nullable();

            $table->timestamps();

            $table->index(['magazine_issue_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_summary_entries');
    }
};
