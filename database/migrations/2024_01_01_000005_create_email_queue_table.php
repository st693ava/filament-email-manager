<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smtp_server_id')
                ->constrained('smtp_servers')
                ->cascadeOnDelete();
            $table->foreignId('template_id')
                ->constrained('email_templates')
                ->cascadeOnDelete();
            $table->json('recipients'); // {"to": ["email1"], "cc": ["email2"], "bcc": ["email3"]}
            $table->json('data'); // Data for template placeholders
            $table->json('attachments')->nullable(); // Files to attach
            $table->integer('priority')->default(0); // Higher = more priority
            $table->timestamp('scheduled_at')->nullable(); // When to send (null = ASAP)
            $table->integer('attempts')->default(0);
            $table->timestamp('last_attempt_at')->nullable();
            $table->enum('status', ['pending', 'processing', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['scheduled_at']);
            $table->index(['priority']);
            $table->index(['smtp_server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_queue');
    }
};