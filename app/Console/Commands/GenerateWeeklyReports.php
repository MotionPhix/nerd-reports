<?php

namespace App\Console\Commands;

use App\Jobs\GenerateWeeklyReportsJob;
use App\Models\User;
use App\Services\ReportGenerationService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerateWeeklyReports extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'reports:generate-weekly
                            {--user= : Generate report for specific user ID}
                            {--week= : Week number (default: current week)}
                            {--year= : Year (default: current year)}
                            {--queue : Queue the job instead of running immediately}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Generate weekly reports for users';

  /**
   * Execute the console command.
   */
  public function handle(ReportGenerationService $reportService): int
  {
    $this->info('🚀 Starting weekly report generation...');

    try {
      if ($this->option('queue')) {
        GenerateWeeklyReportsJob::dispatch();
        $this->info('✅ Weekly report generation job queued successfully');
        return self::SUCCESS;
      }

      $userId = $this->option('user');
      $week = $this->option('week') ?? now()->weekOfYear;
      $year = $this->option('year') ?? now()->year;

      if ($userId) {
        // Generate for specific user
        $user = User::find($userId);
        if (!$user) {
          $this->error("❌ User with ID {$userId} not found");
          return self::FAILURE;
        }

        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();

        $this->info("📊 Generating report for {$user->name} (Week {$week}, {$year})");

        $report = $reportService->generateWeeklyReport($user, $startOfWeek, $endOfWeek);

        $this->info("✅ Report generated successfully: {$report->title}");
        $this->table(['Metric', 'Value'], [
          ['Report ID', $report->uuid],
          ['Total Hours', $report->total_hours],
          ['Total Tasks', $report->total_tasks],
          ['Completed Tasks', $report->completed_tasks],
          ['Projects', $report->reportItems()->count()],
        ]);

      } else {
        // Generate for all users
        $this->info("📊 Generating weekly reports for all users (Week {$week}, {$year})");

        $reports = $reportService->generateAutomaticWeeklyReports();

        $this->info("✅ Generated {$reports->count()} reports successfully");

        if ($reports->isNotEmpty()) {
          $tableData = $reports->map(function ($report) {
            $user = User::find($report->generated_by);
            return [
              $user->name ?? 'Unknown',
              $report->total_hours,
              $report->total_tasks,
              $report->completed_tasks,
              $report->reportItems()->count(),
            ];
          })->toArray();

          $this->table(['User', 'Hours', 'Tasks', 'Completed', 'Projects'], $tableData);
        }
      }

      return self::SUCCESS;

    } catch (\Exception $e) {
      $this->error("❌ Failed to generate weekly reports: {$e->getMessage()}");
      return self::FAILURE;
    }
  }
}
