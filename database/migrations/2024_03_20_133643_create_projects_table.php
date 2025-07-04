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

      $table->uuid('uuid')->unique();

      $table->string('name');

      $table->date('due_date')->nullable();

      $table->longText('description')->nullable();

      $table->enum(
        'status',
        ['in_progress', 'approved', 'completed', 'cancelled'
      ])->default('in_progress');

      $table->foreignUuid('contact_id')->index()->constrained('contacts', 'uuid');

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
