<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\Project;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\ReportTemplate;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportGenerationService
{
  /**
   * Generate a weekly report for a specific user and week
   */
  public function generateWeeklyReport(User $user, Carbon $startOfWeek = null, Carbon $endOfWeek = null): Report
  {
    $startOfWeek = $startOfWeek ?? now()->startOfWeek();
    $endOfWeek = $endOfWeek ?? now()->endOfWeek();

    Log::info("Generating weekly report for user {$user->id} for week {$startOfWeek->format('Y-m-d')} to {$endOfWeek->format('Y-m-d')}");

    return DB::transaction(function () use ($user, $startOfWeek, $endOfWeek) {
      // Create the report
      $report = Report::create([
        'title' => "Weekly Report - Week {$startOfWeek->weekOfYear}, {$startOfWeek->year}",
        'description' => "Automated weekly report for {$startOfWeek->format('M j')} - {$endOfWeek->format('M j, Y')}",
        'report_type' => ReportType::WEEKLY,
        'status' => ReportStatus::GENERATING,
        'week_number' => $startOfWeek->weekOfYear,
        'year' => $startOfWeek->year,
        'month' => $startOfWeek->format('F'),
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek,
        'generated_by' => $user->id,
        'generated_at' => now(),
      ]);

      // Get projects with activity in the specified week
      $projectsWithActivity = $this->getProjectsWithActivity($user, $startOfWeek, $endOfWeek);

      $totalHours = 0;
      $totalTasks = 0;
      $completedTasks = 0;

      // Generate report items for each project
      foreach ($projectsWithActivity as $project) {
        $reportItem = $this->generateReportItemForProject($report, $project, $user, $startOfWeek, $endOfWeek);

        $totalHours += $reportItem->total_hours;
        $totalTasks += $reportItem->task_count;
        $completedTasks += $reportItem->completed_task_count;
      }

      // Update report totals
      $report->update([
        'total_hours' => $totalHours,
        'total_tasks' => $totalTasks,
        'completed_tasks' => $completedTasks,
        'status' => ReportStatus::GENERATED,
        'metadata' => [
          'projects_count' => $projectsWithActivity->count(),
          'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
          'average_hours_per_project' => $projectsWithActivity->count() > 0 ? round($totalHours / $projectsWithActivity->count(), 2) : 0,
          'generated_at' => now()->toISOString(),
        ]
      ]);

      Log::info("Weekly report generated successfully", [
        'report_id' => $report->uuid,
        'total_hours' => $totalHours,
        'total_tasks' => $totalTasks,
        'projects_count' => $projectsWithActivity->count()
      ]);

      return $report;
    });
  }

  /**
   * Generate a custom date range report
   */
  public function generateCustomReport(User $user, Carbon $startDate, Carbon $endDate, array $options = []): Report
  {
    Log::info("Generating custom report for user {$user->id} from {$startDate->format('Y-m-d')} to {$endDate->format('Y-m-d')}");

    return DB::transaction(function () use ($user, $startDate, $endDate, $options) {
      $report = Report::create([
        'title' => $options['title'] ?? "Custom Report - {$startDate->format('M j')} to {$endDate->format('M j, Y')}",
        'description' => $options['description'] ?? "Custom date range report",
        'report_type' => ReportType::CUSTOM,
        'status' => ReportStatus::GENERATING,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'year' => $startDate->year,
        'month' => $startDate->format('F'),
        'generated_by' => $user->id,
        'generated_at' => now(),
      ]);

      // Apply filters if specified
      $projectsQuery = Project::query();

      if (isset($options['contact_id'])) {
        $projectsQuery->where('contact_id', $options['contact_id']);
      }

      if (isset($options['firm_id'])) {
        $projectsQuery->whereHas('contact', function ($query) use ($options) {
          $query->where('firm_id', $options['firm_id']);
        });
      }

      $projectsWithActivity = $projectsQuery->withActivityInWeek($startDate, $endDate, $user->id)->get();

      $totalHours = 0;
      $totalTasks = 0;
      $completedTasks = 0;

      foreach ($projectsWithActivity as $project) {
        $reportItem = $this->generateReportItemForProject($report, $project, $user, $startDate, $endDate);

        $totalHours += $reportItem->total_hours;
        $totalTasks += $reportItem->task_count;
        $completedTasks += $reportItem->completed_task_count;
      }

      $report->update([
        'total_hours' => $totalHours,
        'total_tasks' => $totalTasks,
        'completed_tasks' => $completedTasks,
        'status' => ReportStatus::GENERATED,
        'metadata' => array_merge([
          'projects_count' => $projectsWithActivity->count(),
          'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0,
          'date_range_days' => $startDate->diffInDays($endDate) + 1,
          'generated_at' => now()->toISOString(),
        ], $options['metadata'] ?? [])
      ]);

      return $report;
    });
  }

  /**
   * Generate a project-specific report
   */
  public function generateProjectReport(User $user, Project $project, Carbon $startDate = null, Carbon $endDate = null): Report
  {
    $startDate = $startDate ?? $project->created_at;
    $endDate = $endDate ?? now();

    return DB::transaction(function () use ($user, $project, $startDate, $endDate) {
      $report = Report::create([
        'title' => "Project Report - {$project->name}",
        'description' => "Comprehensive report for project: {$project->name}",
        'report_type' => ReportType::PROJECT_SPECIFIC,
        'status' => ReportStatus::GENERATING,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'year' => $startDate->year,
        'month' => $startDate->format('F'),
        'generated_by' => $user->id,
        'generated_at' => now(),
      ]);

      $reportItem = $this->generateReportItemForProject($report, $project, $user, $startDate, $endDate);

      $report->update([
        'total_hours' => $reportItem->total_hours,
        'total_tasks' => $reportItem->task_count,
        'completed_tasks' => $reportItem->completed_task_count,
        'status' => ReportStatus::GENERATED,
        'metadata' => [
          'project_id' => $project->uuid,
          'project_name' => $project->name,
          'contact_name' => $project->contact->full_name ?? null,
          'firm_name' => $project->contact->firm->name ?? null,
          'project_status' => $project->status->value,
          'completion_rate' => $reportItem->task_count > 0 ? round(($reportItem->completed_task_count / $reportItem->task_count) * 100, 1) : 0,
          'generated_at' => now()->toISOString(),
        ]
      ]);

      return $report;
    });
  }

  /**
   * Get projects with activity for a user in a date range
   */
  private function getProjectsWithActivity(User $user, Carbon $startDate, Carbon $endDate): Collection
  {
    return Project::withActivityInWeek($startDate, $endDate, $user->id)
      ->with(['contact.firm', 'tasks' => function ($query) use ($startDate, $endDate, $user) {
        $query->workedOnInWeek($startDate, $endDate)
          ->forUser($user->id);
      }])
      ->get();
  }

  /**
   * Generate a report item for a specific project
   */
  private function generateReportItemForProject(Report $report, Project $project, User $user, Carbon $startDate, Carbon $endDate): ReportItem
  {
    $tasks = $project->getTasksWorkedOnInWeek($startDate, $endDate, $user->id);
    $completedTasks = $project->getCompletedTasksInWeek($startDate, $endDate, $user->id);
    $totalHours = $project->getTotalHoursInWeek($startDate, $endDate, $user->id);

    // Prepare tasks data for storage
    $tasksData = $tasks->map(function ($task) {
      return [
        'id' => $task->uuid,
        'name' => $task->name,
        'description' => strip_tags($task->description),
        'status' => $task->status->value,
        'priority' => $task->priority->value,
        'estimated_hours' => $task->estimated_hours,
        'actual_hours' => $task->actual_hours,
        'started_at' => $task->started_at?->toISOString(),
        'completed_at' => $task->completed_at?->toISOString(),
        'board_name' => $task->board->name ?? null,
      ];
    })->toArray();

    return ReportItem::create([
      'report_id' => $report->uuid,
      'project_id' => $project->uuid,
      'project_name' => $project->name,
      'contact_name' => $project->contact->full_name ?? null,
      'firm_name' => $project->contact->firm->name ?? null,
      'total_hours' => $totalHours,
      'task_count' => $tasks->count(),
      'completed_task_count' => $completedTasks->count(),
      'tasks_data' => $tasksData,
      'notes' => $this->generateProjectNotes($project, $tasks, $completedTasks, $totalHours),
    ]);
  }

  /**
   * Generate notes for a project in the report
   */
  private function generateProjectNotes(Project $project, Collection $tasks, Collection $completedTasks, float $totalHours): string
  {
    $notes = [];

    if ($tasks->isNotEmpty()) {
      $completionRate = round(($completedTasks->count() / $tasks->count()) * 100, 1);
      $notes[] = "Completion rate: {$completionRate}%";
    }

    if ($totalHours > 0) {
      $notes[] = "Total time spent: " . $this->formatHours($totalHours);
    }

    $highPriorityTasks = $tasks->where('priority.value', 'high')->count();
    $urgentTasks = $tasks->where('priority.value', 'urgent')->count();

    if ($urgentTasks > 0) {
      $notes[] = "{$urgentTasks} urgent task(s) worked on";
    }

    if ($highPriorityTasks > 0) {
      $notes[] = "{$highPriorityTasks} high priority task(s) worked on";
    }

    return implode('. ', $notes);
  }

  /**
   * Format hours for display
   */
  private function formatHours(float $hours): string
  {
    $wholeHours = floor($hours);
    $minutes = ($hours - $wholeHours) * 60;

    if ($wholeHours > 0 && $minutes > 0) {
      return "{$wholeHours}h {$minutes}m";
    } elseif ($wholeHours > 0) {
      return "{$wholeHours}h";
    } else {
      return "{$minutes}m";
    }
  }

  /**
   * Get the default template for a report type
   */
  public function getDefaultTemplate(ReportType $reportType): ?ReportTemplate
  {
    return ReportTemplate::where('report_type', $reportType)
      ->where('is_default', true)
      ->where('is_active', true)
      ->first();
  }

  /**
   * Generate automatic weekly reports for all users
   */
  public function generateAutomaticWeeklyReports(): Collection
  {
    $users = User::whereHas('assignedTasks')->get();
    $reports = collect();

    foreach ($users as $user) {
      try {
        $report = $this->generateWeeklyReport($user);
        $reports->push($report);

        Log::info("Automatic weekly report generated for user {$user->id}", [
          'report_id' => $report->uuid
        ]);
      } catch (\Exception $e) {
        Log::error("Failed to generate automatic weekly report for user {$user->id}", [
          'error' => $e->getMessage()
        ]);
      }
    }

    return $reports;
  }
}
