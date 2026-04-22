<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Numéros magazine (EP-06) : papier + PDF, avec sommaire et stock.
 * Prix en centimes XOF. Le PDF est servi via un lecteur protégé avec watermark.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magazine_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('number')->unique();
            $table->string('title');
            $table->text('theme')->nullable();
            $table->string('slug')->unique();

            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();

            $table->string('pdf_disk')->nullable();
            $table->string('pdf_path')->nullable();
            $table->unsignedInteger('pdf_size_bytes')->nullable();
            $table->unsignedInteger('pages_count')->nullable();

            $table->date('publication_date');

            $table->unsignedBigInteger('price_paper_cents')->nullable();
            $table->unsignedBigInteger('price_pdf_cents')->nullable();
            $table->string('currency', 3)->default('XOF');
            $table->unsignedInteger('stock_paper')->default(0);

            $table->enum('status', ['draft', 'scheduled', 'published', 'archived'])
                ->default('draft')->index();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'publication_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magazine_issues');
    }
};
