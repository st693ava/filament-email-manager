<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('host');
            $table->integer('port')->default(587);
            $table->string('encryption')->nullable(); // null, 'ssl', 'tls'
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Encrypted
            $table->string('from_email');
            $table->string('from_name');
            $table->integer('rate_limit_per_hour')->default(0); // 0 = unlimited
            $table->json('settings')->nullable(); // Additional SMTP settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['is_active']);
            $table->index(['is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_servers');
    }
};