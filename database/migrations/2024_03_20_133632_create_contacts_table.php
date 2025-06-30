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
    Schema::create('contacts', function (Blueprint $table) {
      // Primary key - UUID
      $table->uuid('uuid')->primary();

      // Basic contact information
      $table->string('first_name');
      $table->string('last_name');
      $table->string('middle_name')->nullable();
      $table->string('nickname')->nullable();

      // Professional information
      $table->string('job_title')->nullable();
      $table->string('title')->nullable(); // Mr., Mrs., Dr., etc.
      $table->text('bio')->nullable();

      // Firm relationship
      $table->uuid('firm_id')->nullable();
      $table->foreign('firm_id')->references('uuid')->on('firms')->onDelete('set null');

      // Activity tracking
      $table->timestamp('last_viewed_at')->nullable();
      $table->timestamp('last_interaction_at')->nullable();

      // Metadata
      $table->json('metadata')->nullable();
      $table->text('notes')->nullable();

      // Timestamps
      $table->timestamps();
      $table->softDeletes();

      // Indexes for performance
      $table->index(['first_name', 'last_name']);
      $table->index('firm_id');
      $table->index('job_title');
      $table->index('created_at');
      $table->index('deleted_at');
      $table->index('last_viewed_at');
      $table->index('last_interaction_at');

      // Full-text search index for better search performance
      $table->fullText(['first_name', 'last_name', 'middle_name', 'nickname', 'job_title', 'bio']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('contacts');
  }
};
