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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('repository_id')->constrained()->cascadeOnDelete();
            $table->string('external_id')->nullable(); // GitHub issue or Jira ticket ID
            $table->string('external_url')->nullable(); // Link to external system
            $table->string('provider'); // GitHub, Jira, etc.
            $table->string('status')->default('pending'); // pending, in_progress, completed, failed
            $table->string('type')->default('implementation'); // implementation, review, story_management
            $table->string('branch_name')->nullable(); // Branch created for the task if any
            $table->string('pull_request_url')->nullable(); // URL to PR if created
            $table->json('metadata')->nullable(); // Additional task information
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
