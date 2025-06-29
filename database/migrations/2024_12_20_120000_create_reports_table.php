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
    Schema::create('reports', function (Blueprint $table) {
      $table->id();

      $table->uuid('uuid')->unique();

      $table->string('title');

      $table->text('description')->nullable();

      $table->enum('report_type', ['weekly', 'custom', 'monthly', 'project_specific', 'client_specific'])->default('weekly');

      $table->enum('status', ['draft', 'generating', 'generated', 'sending', 'sent', 'failed'])->default('draft');

      $table->integer('week_number')->nullable();

      $table->integer('year')->nullable();

      $table->string('month')->nullable();

      $table->date('start_date');

      $table->date('end_date');

      $table->foreignId('generated_by')->index()->constrained('users');

      $table->timestamp('generated_at')->nullable();

      $table->timestamp('sent_at')->nullable();

      $table->decimal('total_hours', 8, 2)->default(0);

      $table->integer('total_tasks')->default(0);

      $table->integer('completed_tasks')->default(0);

      $table->json('metadata')->nullable();

      $table->timestamps();

      // Indexes for better query performance
      $table->index(['year', 'week_number']);
      $table->index(['start_date', 'end_date']);
      $table->index(['report_type', 'status']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('reports');
  }
};
