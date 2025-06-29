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
    Schema::table('firms', function (Blueprint $table) {
      $table->text('description')->nullable()->after('url');
      $table->string('industry', 100)->nullable()->after('description');
      $table->enum('size', ['small', 'medium', 'large', 'enterprise'])->nullable()->after('industry');

      // Status and priority
      $table->enum('status', ['active', 'inactive', 'prospect'])->default('prospect')->after('logo_url');
      $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->after('status');

      // Source and assignment
      $table->string('source', 100)->nullable()->after('priority');
      $table->unsignedBigInteger('assigned_to')->nullable()->after('source');

      // Social media URLs
      $table->string('linkedin_url')->nullable()->after('assigned_to');
      $table->string('twitter_url')->nullable()->after('linkedin_url');
      $table->string('facebook_url')->nullable()->after('twitter_url');

      // Financial information
      $table->decimal('total_revenue', 15, 2)->nullable()->after('facebook_url');

      // Additional metadata and notes
      $table->json('metadata')->nullable()->after('total_revenue');
      $table->text('notes')->nullable()->after('metadata');

      // Add foreign key constraint for assigned_to
      $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');

      // Add indexes for better performance
      $table->index('status');
      $table->index('industry');
      $table->index('size');
      $table->index('priority');
      $table->index('assigned_to');
      $table->index('created_at');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('firms', function (Blueprint $table) {
      // Drop foreign key first
      $table->dropForeign(['assigned_to']);

      // Drop indexes
      $table->dropIndex(['status']);
      $table->dropIndex(['industry']);
      $table->dropIndex(['size']);
      $table->dropIndex(['priority']);
      $table->dropIndex(['assigned_to']);
      $table->dropIndex(['created_at']);

      // Drop columns
      $table->dropColumn([
        'description',
        'industry',
        'size',
        'status',
        'priority',
        'source',
        'assigned_to',
        'linkedin_url',
        'twitter_url',
        'facebook_url',
        'total_revenue',
        'metadata',
        'notes',
      ]);
    });
  }
};
