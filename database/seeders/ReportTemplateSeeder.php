<?php

namespace Database\Seeders;

use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportTemplateSeeder extends Seeder
{
  /**
   * Run the database seeder.
   */
  public function run(): void
  {
    // Get the first user (should be the super admin)
    $user = User::first();

    if (!$user) {
      $this->command->warn('No users found. Skipping report template seeding.');
      return;
    }

    // Create default weekly report template
    ReportTemplate::create([
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
      'created_by' => $user->id,
      'settings' => [
        'include_time_breakdown' => true,
        'include_task_details' => true,
        'include_project_summary' => true,
        'group_by_project' => true
      ],
    ]);

    // Create default custom report template
    ReportTemplate::create([
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
      'created_by' => $user->id,
      'settings' => [
        'include_time_breakdown' => true,
        'include_task_details' => true,
        'include_project_summary' => true,
        'group_by_project' => true
      ],
    ]);

    // Create monthly report template
    ReportTemplate::create([
      'name' => 'Monthly Summary Report',
      'description' => 'Comprehensive monthly report template',
      'report_type' => 'monthly',
      'email_subject_template' => 'Monthly Report - {month} {year}',
      'email_body_template' => "Dear {recipient_name},

Please find attached my monthly summary report for {month} {year}.

Monthly Summary:
- Total Projects Worked On: {total_projects}
- Total Tasks: {total_tasks}
- Completed Tasks: {completed_tasks}
- Total Hours: {total_hours}
- Average Weekly Hours: {average_weekly_hours}

This report provides a comprehensive overview of all work completed during the month.

Best regards,
{sender_name}",
      'is_default' => false,
      'is_active' => true,
      'created_by' => $user->id,
      'settings' => [
        'include_time_breakdown' => true,
        'include_task_details' => false,
        'include_project_summary' => true,
        'group_by_project' => true,
        'include_weekly_breakdown' => true
      ],
    ]);

    $this->command->info('Report templates seeded successfully!');
  }
}
