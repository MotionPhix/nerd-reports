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
    Schema::create('projects', function (Blueprint $table) {
      $table->id();

      $table->uuid('pid')->nullable();

      $table->string('name');

      $table->date('due_date')->nullable();

      $table->longText('description')->nullable();

      $table->enum(
        'status',
        ['completed', 'approved', 'in_progress', 'cancelled'
      ])->default('in_progress');

      $table->foreignId('contact_id')->index()->constrained('contacts');

      $table->foreignId('created_by')->index()->constrained('users');

      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('projects');
  }
};
