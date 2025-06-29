<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
  protected TaskManagementService $taskService;
  protected ProjectManagementService $projectService;
  protected InteractionService $interactionService;
  protected ReportGenerationService $reportService;

  public function __construct(
    TaskManagementService    $taskService,
    ProjectManagementService $projectService,
    InteractionService       $interactionService,
    ReportGenerationService  $reportService
  )
  {
    $this->taskService = $taskService;
    $this->projectService = $projectService;
    $this->interactionService = $interactionService;
    $this->reportService = $reportService;
  }

  /**
   * Get comprehensive dashboard data for a user
   */
  public function getDashboardData(User $user): array
  {
    $cacheKey = "dashboard_data_user_{$user->id}_" . now()->format('Y-m-d-H');

    return Cache::remember($cacheKey, 3600, function () use ($user) {
      return [
        'overview' => $this->getOverviewStats($user),
        'tasks' => $this->getTasksData($user),
        'projects' => $this->getProjectsData($user),
        'interactions' => $this->getInteractionsData($user),
        'reports' => $this->getReportsData($user),
        'upcoming' => $this->getUpcomingItems($user),
        'recent_activity' => $this->getRecentActivity($user),
        'productivity' => $this->getProductivityMetrics($user),
      ];
    });
  }

  /**
   * Get overview statistics
   */
  private function getOverviewStats(User $user): array
  {
    $thisWeek = [now()->startOfWeek(), now()->endOfWeek()];
    $thisMonth = [now()->startOfMonth(), now()->endOfMonth()];

    return [
      'tasks' => [
        'total_assigned' => $user->assignedTasks()->count(),
        'completed_this_week' => $user->assignedTasks()
          ->where('status', 'completed')
          ->whereBetween('completed_at', $thisWeek)
          ->count(),
        'overdue' => $this->taskService->getOverdueTasks($user)->count(),
        'in_progress' => $user->assignedTasks()->where('status', 'in_progress')->count(),
      ],
      'projects' => [
        'total' => $this->projectService->getUserProjects($user)->count(),
        'active' => $this->projectService->getUserProjects($user, ['status' => 'in_progress'])->count(),
        'overdue' => $this->projectService->getOverdueProjects($user)->count(),
        'completed_this_month' => $this->projectService->getUserProjects($user, [
          'status' => 'completed',
          'due_date_from' => $thisMonth[0],
          'due_date_to' => $thisMonth[1],
        ])->count(),
      ],
      'interactions' => [
        'this_week' => $this->interactionService->getUserInteractions($user, [
          'date_from' => $thisWeek[0],
          'date_to' => $thisWeek[1],
        ])->count(),
        'follow_ups_overdue' => $this->interactionService->getOverdueFollowUps($user)->count(),
        'follow_ups_today' => $this->interactionService->getTodayFollowUps($user)->count(),
      ],
      'reports' => [
        'generated_this_month' => $user->generatedReports()
          ->whereBetween('generated_at', $thisMonth)
          ->count(),
        'pending' => $user->generatedReports()
          ->whereIn('status', ['draft', 'generating'])
          ->count(),
      ],
    ];
  }

  /**
   * Get tasks data for dashboard
   */
  private function getTasksData(User $user): array
  {
    $recentTasks = $this->taskService->getUserTasks($user, [
      'order_by' => 'updated_at',
      'order_direction' => 'desc',
    ])->take(5);

    $overdueTasks = $this->taskService->getOverdueTasks($user)->take(5);

    $taskStats = $this->taskService->getUserTaskStats($user);

    return [
      'recent' => $recentTasks,
      'overdue' => $overdueTasks,
      'stats' => $taskStats,
      'priority_distribution' => $taskStats['priority_breakdown'],
    ];
  }

  /**
   * Get projects data for dashboard
   */
  private function getProjectsData(User $user): array
  {
    $activeProjects = $this->projectService->getUserProjects($user, [
      'status' => 'in_progress',
    ])->take(5);

    $recentlyActive = $this->projectService->getRecentlyActiveProjects($user, 7);

    $overdueProjects = $this->projectService->getOverdueProjects($user)->take(3);

    return [
      'active' => $activeProjects->map(function ($project) {
        return array_merge($project->toArray(), [
          'progress' => $this->projectService->getProjectProgress($project),
        ]);
      }),
      'recently_active' => $recentlyActive->take(5),
      'overdue' => $overdueProjects,
    ];
  }

  /**
   * Get interactions data for dashboard
   */
  private function getInteractionsData(User $user): array
  {
    $recentInteractions = $this->interactionService->getRecentInteractions($user, 5);
    $overdueFollowUps = $this->interactionService->getOverdueFollowUps($user)->take(5);
    $todayFollowUps = $this->interactionService->getTodayFollowUps($user);
    $upcomingFollowUps = $this->interactionService->getUpcomingFollowUps($user, 7)->take(5);

    $interactionStats = $this->interactionService->getUserInteractionStats($user);

    return [
      'recent' => $recentInteractions,
      'overdue_follow_ups' => $overdueFollowUps,
      'today_follow_ups' => $todayFollowUps,
      'upcoming_follow_ups' => $upcomingFollowUps,
      'stats' => $interactionStats,
    ];
  }

  /**
   * Get reports data for dashboard
   */
  private function getReportsData(User $user): array
  {
    $recentReports = $user->generatedReports()
      ->with(['reportItems'])
      ->orderBy('generated_at', 'desc')
      ->take(5)
      ->get();

    $pendingReports = $user->generatedReports()
      ->whereIn('status', ['draft', 'generating'])
      ->get();

    return [
      'recent' => $recentReports,
      'pending' => $pendingReports,
      'last_weekly_report' => $user->generatedReports()
        ->where('report_type', 'weekly')
        ->orderBy('generated_at', 'desc')
        ->first(),
    ];
  }

  /**
   * Get upcoming items (deadlines, follow-ups, etc.)
   */
  private function getUpcomingItems(User $user): array
  {
    $upcomingTasks = $this->taskService->getUserTasks($user, [
      'due_date_from' => now(),
      'due_date_to' => now()->addDays(7),
    ])->sortBy('due_date');

    $upcomingProjects = $this->projectService->getUserProjects($user, [
      'due_date_from' => now(),
      'due_date_to' => now()->addDays(14),
    ])->sortBy('due_date');

    $upcomingFollowUps = $this->interactionService->getUpcomingFollowUps($user, 7);

    return [
      'tasks' => $upcomingTasks->take(5),
      'projects' => $upcomingProjects->take(3),
      'follow_ups' => $upcomingFollowUps->take(5),
    ];
  }

  /**
   * Get recent activity across all areas
   */
  private function getRecentActivity(User $user): array
  {
    $activities = collect();

    // Recent task updates
    $recentTasks = $user->assignedTasks()
      ->where('updated_at', '>=', now()->subDays(7))
      ->orderBy('updated_at', 'desc')
      ->take(5)
      ->get();

    foreach ($recentTasks as $task) {
      $activities->push([
        'type' => 'task',
        'action' => $this->getTaskAction($task),
        'item' => $task,
        'timestamp' => $task->updated_at,
        'description' => "Task '{$task->name}' was {$this->getTaskAction($task)}",
      ]);
    }

    // Recent interactions
    $recentInteractions = $this->interactionService->getRecentInteractions($user, 5);
    foreach ($recentInteractions as $interaction) {
      $activities->push([
        'type' => 'interaction',
        'action' => 'created',
        'item' => $interaction,
        'timestamp' => $interaction->interaction_date,
        'description' => "{$interaction->type->getLabel()} with {$interaction->contact->full_name}",
      ]);
    }

    // Recent reports
    $recentReports = $user->generatedReports()
      ->where('generated_at', '>=', now()->subDays(7))
      ->orderBy('generated_at', 'desc')
      ->take(3)
      ->get();

    foreach ($recentReports as $report) {
      $activities->push([
        'type' => 'report',
        'action' => 'generated',
        'item' => $report,
        'timestamp' => $report->generated_at,
        'description' => "Report '{$report->title}' was generated",
      ]);
    }

    return $activities->sortByDesc('timestamp')->take(10)->values()->all();
  }

  /**
   * Get productivity metrics
   */
  private function getProductivityMetrics(User $user): array
  {
    $thisWeek = [now()->startOfWeek(), now()->endOfWeek()];
    $lastWeek = [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()];
    $thisMonth = [now()->startOfMonth(), now()->endOfMonth()];

    // This week's stats
    $thisWeekTasks = $user->assignedTasks()
      ->whereBetween('updated_at', $thisWeek)
      ->get();

    $thisWeekCompleted = $thisWeekTasks->where('status.value', 'completed')->count();
    $thisWeekHours = $thisWeekTasks->sum('actual_hours');

    // Last week's stats for comparison
    $lastWeekTasks = $user->assignedTasks()
      ->whereBetween('updated_at', $lastWeek)
      ->get();

    $lastWeekCompleted = $lastWeekTasks->where('status.value', 'completed')->count();
    $lastWeekHours = $lastWeekTasks->sum('actual_hours');

    // Calculate trends
    $taskTrend = $lastWeekCompleted > 0 ?
      round((($thisWeekCompleted - $lastWeekCompleted) / $lastWeekCompleted) * 100, 1) : 0;

    $hoursTrend = $lastWeekHours > 0 ?
      round((($thisWeekHours - $lastWeekHours) / $lastWeekHours) * 100, 1) : 0;

    return [
      'this_week' => [
        'completed_tasks' => $thisWeekCompleted,
        'total_hours' => $thisWeekHours,
        'average_hours_per_task' => $thisWeekCompleted > 0 ?
          round($thisWeekHours / $thisWeekCompleted, 1) : 0,
      ],
      'trends' => [
        'tasks' => $taskTrend,
        'hours' => $hoursTrend,
      ],
      'monthly_goal_progress' => $this->calculateMonthlyGoalProgress($user, $thisMonth),
    ];
  }

  /**
   * Calculate monthly goal progress (example implementation)
   */
  private function calculateMonthlyGoalProgress(User $user, array $monthRange): array
  {
    // This could be configurable per user
    $monthlyTaskGoal = 50; // Example goal
    $monthlyHourGoal = 160; // Example goal

    $monthlyTasks = $user->assignedTasks()
      ->where('status', 'completed')
      ->whereBetween('completed_at', $monthRange)
      ->count();

    $monthlyHours = $user->assignedTasks()
      ->whereBetween('updated_at', $monthRange)
      ->sum('actual_hours');

    return [
      'tasks' => [
        'completed' => $monthlyTasks,
        'goal' => $monthlyTaskGoal,
        'percentage' => round(($monthlyTasks / $monthlyTaskGoal) * 100, 1),
      ],
      'hours' => [
        'logged' => $monthlyHours,
        'goal' => $monthlyHourGoal,
        'percentage' => round(($monthlyHours / $monthlyHourGoal) * 100, 1),
      ],
    ];
  }

  /**
   * Get task action description
   */
  private function getTaskAction($task): string
  {
    return match ($task->status->value) {
      'completed' => 'completed',
      'in_progress' => 'started',
      'review' => 'moved to review',
      'on_hold' => 'put on hold',
      'cancelled' => 'cancelled',
      default => 'updated',
    };
  }

  /**
   * Clear dashboard cache for a user
   */
  public function clearDashboardCache(User $user): void
  {
    $pattern = "dashboard_data_user_{$user->id}_*";
    Cache::forget($pattern);
  }

  /**
   * Get quick stats for header/sidebar
   */
  public function getQuickStats(User $user): array
  {
    return [
      'pending_tasks' => $user->assignedTasks()
        ->whereIn('status', ['todo', 'in_progress'])
        ->count(),
      'overdue_tasks' => $this->taskService->getOverdueTasks($user)->count(),
      'overdue_follow_ups' => $this->interactionService->getOverdueFollowUps($user)->count(),
      'active_projects' => $this->projectService->getUserProjects($user, [
        'status' => 'in_progress'
      ])->count(),
    ];
  }
}
