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

      $table->uuid('uuid')->unique();

      $table->string('name');

      $table->longText('description')->nullable();

      $table->enum('status', ['todo', 'in_progress', 'completed', 'cancelled', 'on_hold', 'review'])->default('todo');

      $table->double('position')->nullable();

      $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

      $table->decimal('estimated_hours', 8, 2)->nullable();

      $table->decimal('actual_hours', 8, 2)->nullable();

      $table->timestamp('started_at')->nullable();

      $table->timestamp('completed_at')->nullable();

      $table->timestamp('due_date')->nullable();

      $table->text('notes')->nullable();

      $table->foreignUuid('board_id')->index()->constrained('boards', 'uuid')->onDelete('cascade');

      $table->foreignUuid('project_id')->nullable()->index()->constrained('projects', 'uuid')->onDelete('cascade');

      $table->foreignId('assigned_to')->index()->constrained('users');

      $table->foreignId('assigned_by')->nullable()->constrained('users');

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
