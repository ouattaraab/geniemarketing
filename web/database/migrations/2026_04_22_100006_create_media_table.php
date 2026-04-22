<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bibliothèque média (EP-05) : images, vidéos, PDFs, infographies.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')
                ->nullable()
                ->constrained('media_collections')
                ->nullOnDelete();
            $table->foreignId('uploaded_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->enum('type', ['image', 'video', 'pdf', 'audio', 'other'])->default('image');
            $table->string('disk')->default('s3');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type', 127);
            $table->unsignedBigInteger('size_bytes');

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();

            $table->string('alt')->nullable();
            $table->string('caption')->nullable();
            $table->string('credit')->nullable();
            $table->string('license')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('uploaded_by_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
