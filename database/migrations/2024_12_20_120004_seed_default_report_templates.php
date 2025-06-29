<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    // Insert default weekly report template
    DB::table('report_templates')->insert([
      'uuid' => \Illuminate\Support\Str::uuid(),
      'name' => 'Default Weekly Report',
      'description' => 'Standard weekly report template for automatic Friday reports',
      'report_type' => 'weekly',
      'email_subject_template' => 'Weekly Report - Week {week_number}, {year}',
      'email_body_template' => "Dear {recipient_name},

Please find attached my weekly report for {week_description}.

Summary:
- Total Projects Worked On: {total_projects}
- Total Tasks: {total_tasks}
- Completed Tasks: {completed_tasks}
- Total Hours: {total_hours}

This report covers all tasks and projects I worked on during the specified week.

Best regards,
{sender_name}",
      'is_default' => true,
      'is_active' => true,
      'created_by' => 1, // Assuming first user
      'settings' => json_encode([
        'include_time_breakdown' => true,
        'include_task_details' => true,
        'include_project_summary' => true,
        'group_by_project' => true
      ]),
      'created_at' => now(),
      'updated_at' => now(),
    ]);

    // Insert default custom report template
    DB::table('report_templates')->insert([
      'uuid' => \Illuminate\Support\Str::uuid(),
      'name' => 'Custom Date Range Report',
      'description' => 'Template for custom date range reports',
      'report_type' => 'custom',
      'email_subject_template' => 'Project Report - {start_date} to {end_date}',
      'email_body_template' => "Dear {recipient_name},

Please find attached my project report for the period from {start_date} to {end_date}.

Summary:
- Total Projects Worked On: {total_projects}
- Total Tasks: {total_tasks}
- Completed Tasks: {completed_tasks}
- Total Hours: {total_hours}

This report covers all tasks and projects I worked on during the specified period.

Best regards,
{sender_name}",
      'is_default' => true,
      'is_active' => true,
      'created_by' => 1, // Assuming first user
      'settings' => json_encode([
        'include_time_breakdown' => true,
        'include_task_details' => true,
        'include_project_summary' => true,
        'group_by_project' => true
      ]),
      'created_at' => now(),
      'updated_at' => now(),
    ]);
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    DB::table('report_templates')->where('name', 'Default Weekly Report')->delete();
    DB::table('report_templates')->where('name', 'Custom Date Range Report')->delete();
  }
};
