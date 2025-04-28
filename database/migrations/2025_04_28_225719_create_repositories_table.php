<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('repositories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url');
            $table->string('provider'); // GitHub, Gitlab, etc.
            $table->string('default_branch')->default('main');
            $table->string('language')->nullable(); // Primary programming language
            $table->json('languages')->nullable(); // All detected languages
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // For storing additional repository information
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repositories');
    }
};
