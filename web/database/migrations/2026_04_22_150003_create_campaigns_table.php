<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Campagnes email (EP-10) — une par envoi, liée à une newsletter.
 * Contenu stocké en Markdown ; rendu via la Mailable.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('newsletter_id')
                ->constrained('newsletters')
                ->restrictOnDelete();
            $table->foreignId('created_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('content');
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();

            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed'])
                ->default('draft')->index();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();

            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('sent_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->unsignedInteger('unsubscribed_count')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['newsletter_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
