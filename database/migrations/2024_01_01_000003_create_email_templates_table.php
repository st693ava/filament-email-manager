<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('subject');
            $table->longText('content_html');
            $table->longText('content_text')->nullable();
            $table->foreignId('layout_id')
                ->nullable()
                ->constrained('email_template_layouts')
                ->nullOnDelete();
            $table->json('placeholders')->nullable(); // [{"name": "customer_name", "description": "...", "required": true}]
            $table->json('merge_tags')->nullable(); // ["customer_name", "company_name"]
            $table->json('default_values')->nullable(); // {"company_name": "Acme Corp"}
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['slug']);
            $table->index(['is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};