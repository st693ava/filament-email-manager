<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('smtp_server_id')
                ->constrained('smtp_servers')
                ->cascadeOnDelete();
            $table->foreignId('template_id')
                ->nullable()
                ->constrained('email_templates')
                ->nullOnDelete();
            $table->json('recipients'); // {"to": ["email1"], "cc": ["email2"], "bcc": ["email3"]}
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_text')->nullable();
            $table->json('attachments')->nullable(); // [{"name": "file.pdf", "path": "/path/to/file", "mime": "application/pdf", "size": 1024}]
            $table->json('headers')->nullable(); // Custom email headers
            $table->json('metadata')->nullable(); // Additional data (merge data used, etc.)
            $table->enum('status', ['pending', 'sending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->string('eml_file_path')->nullable(); // Path to the generated .eml file
            $table->timestamps();

            $table->index(['smtp_server_id']);
            $table->index(['template_id']);
            $table->index(['status']);
            $table->index(['sent_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
    }
};