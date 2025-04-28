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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // codebase_analysis, implementation, review, story_management
            $table->string('language')->nullable(); // PHP, Python, JavaScript, etc. for language-specific agents
            $table->boolean('enabled')->default(true);
            $table->json('configuration')->nullable(); // Agent-specific configuration
            $table->text('description')->nullable();
            $table->json('capabilities')->nullable(); // What the agent can do
            $table->json('metadata')->nullable(); // For storing additional agent information
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
