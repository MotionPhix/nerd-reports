<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ReportTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
  /**
   * Generate PDF for a report
   */
  public function generateReportPdf(Report $report, ReportTemplate $template = null): string
  {
    $template = $template ?? $this->getDefaultTemplate($report);

    if (!$template) {
      throw new \Exception("No template found for report type: {$report->report_type->value}");
    }

    $data = $this->prepareReportData($report);

    // Generate PDF using the appropriate view
    $viewName = $this->getViewName($report->report_type->value);
    $pdf = Pdf::loadView($viewName, $data);

    // Configure PDF settings
    $pdf->setPaper('A4', 'portrait');
    $pdf->setOptions([
      'isHtml5ParserEnabled' => true,
      'isPhpEnabled' => true,
      'defaultFont' => 'Arial',
    ]);

    // Generate filename and save
    $filename = $this->generateFilename($report);
    $path = "reports/pdf/{$filename}";

    Storage::put($path, $pdf->output());

    return $path;
  }

  /**
   * Prepare data for PDF generation
   */
  private function prepareReportData(Report $report): array
  {
    $generator = User::find($report->generated_by);
    $reportItems = $report->reportItems()->with(['project.contact.firm'])->get();

    return [
      'report' => $report,
      'generator' => $generator,
      'reportItems' => $reportItems,
      'metadata' => $report->metadata ?? [],
      'summary' => $this->generateSummary($report, $reportItems),
      'generated_at' => now(),
    ];
  }

  /**
   * Generate summary statistics for the report
   */
  private function generateSummary(Report $report, $reportItems): array
  {
    $totalProjects = $reportItems->count();
    $totalHours = $report->total_hours;
    $totalTasks = $report->total_tasks;
    $completedTasks = $report->completed_tasks;
    $completionRate = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 1) : 0;

    // Calculate additional metrics
    $averageHoursPerProject = $totalProjects > 0 ? round($totalHours / $totalProjects, 2) : 0;
    $averageTasksPerProject = $totalProjects > 0 ? round($totalTasks / $totalProjects, 1) : 0;

    // Get priority breakdown
    $priorityBreakdown = $this->calculatePriorityBreakdown($reportItems);

    // Get status breakdown
    $statusBreakdown = $this->calculateStatusBreakdown($reportItems);

    return [
      'total_projects' => $totalProjects,
      'total_hours' => $totalHours,
      'total_tasks' => $totalTasks,
      'completed_tasks' => $completedTasks,
      'completion_rate' => $completionRate,
      'average_hours_per_project' => $averageHoursPerProject,
      'average_tasks_per_project' => $averageTasksPerProject,
      'priority_breakdown' => $priorityBreakdown,
      'status_breakdown' => $statusBreakdown,
      'date_range' => [
        'start' => $report->start_date,
        'end' => $report->end_date,
        'days' => $report->start_date->diffInDays($report->end_date) + 1,
      ],
    ];
  }

  /**
   * Calculate priority breakdown from report items
   */
  private function calculatePriorityBreakdown($reportItems): array
  {
    $breakdown = [
      'urgent' => 0,
      'high' => 0,
      'medium' => 0,
      'low' => 0,
    ];

    foreach ($reportItems as $item) {
      $tasks = $item->tasks_data ?? [];
      foreach ($tasks as $task) {
        $priority = $task['priority'] ?? 'medium';
        if (isset($breakdown[$priority])) {
          $breakdown[$priority]++;
        }
      }
    }

    return $breakdown;
  }

  /**
   * Calculate status breakdown from report items
   */
  private function calculateStatusBreakdown($reportItems): array
  {
    $breakdown = [
      'completed' => 0,
      'in_progress' => 0,
      'todo' => 0,
      'review' => 0,
      'on_hold' => 0,
      'cancelled' => 0,
    ];

    foreach ($reportItems as $item) {
      $tasks = $item->tasks_data ?? [];
      foreach ($tasks as $task) {
        $status = $task['status'] ?? 'todo';
        if (isset($breakdown[$status])) {
          $breakdown[$status]++;
        }
      }
    }

    return $breakdown;
  }

  /**
   * Get the view name for PDF generation based on report type
   */
  private function getViewName(string $reportType): string
  {
    return match ($reportType) {
      'weekly' => 'reports.pdf.weekly',
      'monthly' => 'reports.pdf.monthly',
      'custom' => 'reports.pdf.custom',
      'project_specific' => 'reports.pdf.project',
      'client_specific' => 'reports.pdf.client',
      default => 'reports.pdf.default',
    };
  }

  /**
   * Generate filename for the PDF
   */
  private function generateFilename(Report $report): string
  {
    $type = ucfirst($report->report_type->value);
    $timestamp = now()->format('Y-m-d_H-i-s');

    if ($report->report_type->value === 'weekly') {
      return "Weekly_Report_Week_{$report->week_number}_{$report->year}_{$timestamp}.pdf";
    }

    $startDate = $report->start_date->format('Y-m-d');
    return "{$type}_Report_{$startDate}_{$timestamp}.pdf";
  }

  /**
   * Get default template for report type
   */
  private function getDefaultTemplate(Report $report): ?ReportTemplate
  {
    return ReportTemplate::where('report_type', $report->report_type)
      ->where('is_default', true)
      ->where('is_active', true)
      ->first();
  }

  /**
   * Generate preview PDF (without saving to storage)
   */
  public function generatePreviewPdf(Report $report, ReportTemplate $template = null): \Barryvdh\DomPDF\PDF
  {
    $template = $template ?? $this->getDefaultTemplate($report);

    if (!$template) {
      throw new \Exception("No template found for report type: {$report->report_type->value}");
    }

    $data = $this->prepareReportData($report);
    $viewName = $this->getViewName($report->report_type->value);

    return Pdf::loadView($viewName, $data)
      ->setPaper('A4', 'portrait')
      ->setOptions([
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled' => true,
        'defaultFont' => 'Arial',
      ]);
  }

  /**
   * Download PDF directly
   */
  public function downloadReportPdf(Report $report, ReportTemplate $template = null): \Symfony\Component\HttpFoundation\Response
  {
    $pdf = $this->generatePreviewPdf($report, $template);
    $filename = $this->generateFilename($report);

    return $pdf->download($filename);
  }

  /**
   * Stream PDF to browser
   */
  public function streamReportPdf(Report $report, ReportTemplate $template = null): \Symfony\Component\HttpFoundation\Response
  {
    $pdf = $this->generatePreviewPdf($report, $template);
    $filename = $this->generateFilename($report);

    return $pdf->stream($filename);
  }
}
