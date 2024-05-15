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
    Schema::create('phones', function (Blueprint $table) {
      $table->id();
      $table->string('number');
      $table->enum('type', ['mobile', 'work', 'home', 'fax'])->default('mobile');
      $table->string('country_code')->nullable();
      $table->boolean('is_primary_phone')->default(false);
      $table->string('formatted')->nullable();
      $table->morphs('phoneable');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('phones');
  }
};
