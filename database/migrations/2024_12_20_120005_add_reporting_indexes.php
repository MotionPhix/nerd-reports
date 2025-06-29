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
        // Add indexes to tasks table for better reporting performance
        Schema::table('tasks', function (Blueprint $table) {
            $table->index(['status', 'completed_at']);
            $table->index(['assigned_to', 'status']);
            $table->index(['created_at', 'updated_at']);
            $table->index(['project_id', 'status']);
        });

        // Add indexes to projects table for better reporting performance
        Schema::table('projects', function (Blueprint $table) {
            $table->index(['created_by', 'status']);
            $table->index(['contact_id', 'status']);
            $table->index(['created_at', 'due_date']);
        });

        // Add indexes to contacts table for better reporting performance
        Schema::table('contacts', function (Blueprint $table) {
            $table->index(['firm_id']);
        });

        // Add indexes to users table for better reporting performance
        Schema::table('users', function (Blueprint $table) {
            $table->index(['email', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['status', 'completed_at']);
            $table->dropIndex(['assigned_to', 'status']);
            $table->dropIndex(['created_at', 'updated_at']);
            $table->dropIndex(['project_id', 'status']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex(['created_by', 'status']);
            $table->dropIndex(['contact_id', 'status']);
            $table->dropIndex(['created_at', 'due_date']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['firm_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['email', 'created_at']);
        });
    }
};
