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

      $table->string('name');

      $table->longText('description')->nullable();

      $table->enum('status', ['done', 'cancelled', 'in_progress'])->default(false);

      $table->double('position')->nullable();

      $table->enum('priority', ['normal', 'medium', 'high'])->default('normal');

      $table->foreignId('board_id')->index()->constrained('boards')->onDelete('cascade');

      $table->foreignId('assigned_to')->index()->constrained('users');

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
