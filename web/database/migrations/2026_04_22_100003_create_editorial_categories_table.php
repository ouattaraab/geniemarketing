<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catégories éditoriales (EP-04) : Succès, Échec constructif, Interview,
 * Portrait, Tribune, Analyse. Indépendantes des rubriques — un article
 * "Succès" peut appartenir à la rubrique "Analyses".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('badge_color_hex', 7)->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('editorial_categories');
    }
};
