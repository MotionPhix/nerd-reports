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
      $table->id();

      $table->uuid('cid')->nullable();

      $table->string('first_name', 50);

      $table->string('last_name', 50);

      $table->string('job_title', 70)->nullable();

      $table->enum('title', ['mr', 'mrs', 'ms', 'sr', 'dr', 'prof'])->nullable();

      $table->string('middle_name', 50)->nullable();

      $table->string('nickname', 50)->nullable();

      $table->text('bio')->nullable();

      $table->softDeletes();

      $table->foreignId('firm_id')->nullable()->constrained('firms');

      $table->timestamps();
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
