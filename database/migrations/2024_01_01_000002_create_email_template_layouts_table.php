<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_layouts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('header_html')->nullable();
            $table->text('footer_html')->nullable();
            $table->longText('wrapper_html'); // Must contain {{content}} placeholder
            $table->longText('css_styles')->nullable();
            $table->json('settings')->nullable(); // Additional layout settings
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_layouts');
    }
};