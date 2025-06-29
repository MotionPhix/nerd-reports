<?php

namespace App\Jobs;

use App\Services\ReportEmailService;
use App\Services\ReportGenerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateWeeklyReportsJob implements ShouldQueue
{
  use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

  /**
   * Create a new job instance.
   */
  public function __construct()
  {
    //
  }

  /**
   * Execute the job.
   */
  public function handle(ReportGenerationService $reportService, ReportEmailService $emailService): void
  {
    Log::info('Starting automatic weekly report generation');

    try {
      // Generate reports for all users
      $reports = $reportService->generateAutomaticWeeklyReports();

      Log::info("Generated {$reports->count()} weekly reports");

      // Get default recipients (this could be configured in settings)
      $defaultRecipients = $emailService->getDefaultReportRecipients();

      if (!empty($defaultRecipients)) {
        // Send reports via email
        $results = $emailService->sendAutomaticWeeklyReports($reports, $defaultRecipients);

        $successCount = collect($results)->where('success', true)->count();
        $totalCount = count($results);

        Log::info("Weekly reports sent: {$successCount}/{$totalCount} successful");
      } else {
        Log::info('No default recipients configured, reports generated but not sent');
      }

    } catch (\Exception $e) {
      Log::error('Failed to generate automatic weekly reports', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      throw $e;
    }
  }
}
