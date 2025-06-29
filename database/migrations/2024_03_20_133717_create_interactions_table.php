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
    Schema::create('interactions', function (Blueprint $table) {
      $table->id();

      $table->uuid('uuid')->unique();

      $table->foreignUuid('contact_id')->index()->constrained('contacts', 'uuid')->onDelete('cascade');

      $table->foreignUuid('project_id')->nullable()->index()->constrained('projects', 'uuid')->onDelete('set null');

      $table->foreignId('user_id')->index()->constrained('users')->onDelete('cascade');

      $table->enum('type', [
        'phone_call', 'email', 'meeting', 'video_call', 'whatsapp',
        'sms', 'in_person', 'slack', 'teams', 'other'
      ])->default('other');

      $table->string('subject');

      $table->longText('description')->nullable();

      $table->text('notes')->nullable();

      $table->integer('duration_minutes')->nullable();

      $table->timestamp('interaction_date');

      $table->boolean('follow_up_required')->default(false);

      $table->date('follow_up_date')->nullable();

      $table->text('outcome')->nullable();

      $table->string('location')->nullable();

      $table->json('participants')->nullable();

      $table->json('metadata')->nullable();

      $table->timestamps();

      // Indexes for better query performance
      $table->index(['contact_id', 'interaction_date']);
      $table->index(['project_id', 'interaction_date']);
      $table->index(['user_id', 'interaction_date']);
      $table->index(['type', 'interaction_date']);
      $table->index(['follow_up_required', 'follow_up_date']);
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('interactions');
  }
};
