<?php

namespace App\Providers;

use App\Services\DashboardService;
use App\Services\InteractionService;
use App\Services\ProjectManagementService;
use App\Services\ReportEmailService;
use App\Services\ReportGenerationService;
use App\Services\ReportPdfService;
use App\Services\TaskManagementService;
use Illuminate\Support\ServiceProvider;

class ReportingServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    // Register all reporting services as singletons
    $this->app->singleton(ReportGenerationService::class);
    $this->app->singleton(ReportPdfService::class);
    $this->app->singleton(ReportEmailService::class);
    $this->app->singleton(TaskManagementService::class);
    $this->app->singleton(ProjectManagementService::class);
    $this->app->singleton(InteractionService::class);
    $this->app->singleton(DashboardService::class);
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // Register console commands
    if ($this->app->runningInConsole()) {
      $this->commands([
        \App\Console\Commands\GenerateWeeklyReports::class,
      ]);
    }
  }
}
