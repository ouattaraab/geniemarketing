<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Commentaires (EP-12) — posts abonnés sous les articles.
 * Modération : tous les commentaires naissent en `pending` avant validation.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')
                ->constrained('articles')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->nullOnDelete();
            $table->foreignId('moderated_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('content');
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])
                ->default('pending')->index();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('report_count')->default(0);
            $table->text('moderation_note')->nullable();
            $table->timestamp('moderated_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['article_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
