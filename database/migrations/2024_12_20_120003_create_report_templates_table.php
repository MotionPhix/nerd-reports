<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('report_templates', function (Blueprint $table) {
      $table->id();

      $table->uuid('uuid')->unique();

      $table->string('name');

      $table->text('description')->nullable();

      $table->enum('report_type', ['weekly', 'custom', 'monthly', 'project_specific', 'client_specific']);

      $table->longText('template_content')->nullable();

      $table->text('email_subject_template')->nullable();

      $table->longText('email_body_template')->nullable();

      $table->boolean('is_default')->default(false);

      $table->boolean('is_active')->default(true);

      $table->foreignId('created_by')->index()->constrained('users');

      $table->json('settings')->nullable();

      $table->timestamps();

      // Indexes for better query performance
      $table->index(['report_type', 'is_active']);
      $table->index(['is_default', 'is_active']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('report_templates');
  }
};
