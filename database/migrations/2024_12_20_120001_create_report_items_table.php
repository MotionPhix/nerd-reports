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
    Schema::create('report_items', function (Blueprint $table) {
      $table->id();

      $table->uuid('uuid')->unique();

      $table->foreignUuid('report_id')->index()->constrained('reports', 'uuid')->onDelete('cascade');

      $table->foreignUuid('project_id')->nullable()->index()->constrained('projects', 'uuid')->onDelete('set null');

      $table->string('project_name');

      $table->string('contact_name')->nullable();

      $table->string('firm_name')->nullable();

      $table->decimal('total_hours', 8, 2)->default(0);

      $table->integer('task_count')->default(0);

      $table->integer('completed_task_count')->default(0);

      $table->json('tasks_data')->nullable();

      $table->text('notes')->nullable();

      $table->timestamps();

      // Indexes for better query performance
      $table->index(['report_id', 'project_id']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('report_items');
  }
};
