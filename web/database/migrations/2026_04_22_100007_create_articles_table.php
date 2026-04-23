<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Table principale des articles éditoriaux (EP-02).
 * Statut : draft → review → scheduled → published → archived.
 * Accès : free · registered · subscriber · premium.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();
            $table->foreignId('editorial_category_id')
                ->nullable()
                ->constrained('editorial_categories')
                ->nullOnDelete();
            $table->foreignId('cover_media_id')
                ->nullable()
                ->constrained('media')
                ->nullOnDelete();
            $table->foreignId('parent_article_id')
                ->nullable()
                ->constrained('articles')
                ->nullOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->string('lede', 500)->nullable();
            $table->json('body')->nullable();

            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('canonical_url')->nullable();

            $table->enum('status', ['draft', 'review', 'scheduled', 'published', 'archived'])
                ->default('draft')->index();
            $table->enum('access_level', ['free', 'registered', 'subscriber', 'premium'])
                ->default('subscriber')->index();
            $table->boolean('is_sponsored')->default(false);
            $table->string('sponsor_name')->nullable();

            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('chapter_position')->nullable();
            $table->unsignedInteger('reading_time_minutes')->nullable();

            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['category_id', 'status']);

            // Full-text uniquement sur MySQL (SQLite/Pgsql gèrent autrement).
            if (DB::connection()->getDriverName() === 'mysql') {
                $table->fullText(['title', 'lede']);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
