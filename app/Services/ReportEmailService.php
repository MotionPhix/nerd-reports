<?php

namespace App\Services;

use App\Enums\ReportStatus;
use App\Models\Contact;
use App\Models\Report;
use App\Models\ReportRecipient;
use App\Models\ReportTemplate;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ReportEmailService
{
  protected ReportPdfService $pdfService;

  public function __construct(ReportPdfService $pdfService)
  {
    $this->pdfService = $pdfService;
  }

  /**
   * Send a report to specified recipients
   */
  public function sendReport(Report $report, array $recipients, ReportTemplate $template = null): bool
  {
    try {
      $report->update(['status' => ReportStatus::SENDING]);

      $template = $template ?? $this->getDefaultTemplate($report);

      if (!$template) {
        throw new \Exception("No template found for report type: {$report->report_type->value}");
      }

      // Generate PDF
      $pdfPath = $this->pdfService->generateReportPdf($report, $template);

      $successCount = 0;
      $totalRecipients = count($recipients);

      foreach ($recipients as $recipientData) {
        try {
          $recipient = $this->createReportRecipient($report, $recipientData);
          $this->sendEmailToRecipient($report, $recipient, $template, $pdfPath);
          $recipient->markAsSent();
          $successCount++;
        } catch (\Exception $e) {
          Log::error("Failed to send report to recipient", [
            'report_id' => $report->uuid,
            'recipient' => $recipientData,
            'error' => $e->getMessage()
          ]);

          if (isset($recipient)) {
            $recipient->markAsFailed($e->getMessage());
          }
        }
      }

      // Update report status
      if ($successCount === $totalRecipients) {
        $report->markAsSent();
        Log::info("Report sent successfully to all recipients", [
          'report_id' => $report->uuid,
          'recipients_count' => $totalRecipients
        ]);
      } elseif ($successCount > 0) {
        $report->update(['status' => ReportStatus::SENT]);
        Log::warning("Report partially sent", [
          'report_id' => $report->uuid,
          'success_count' => $successCount,
          'total_count' => $totalRecipients
        ]);
      } else {
        $report->update(['status' => ReportStatus::FAILED]);
        Log::error("Report failed to send to any recipients", [
          'report_id' => $report->uuid
        ]);
      }

      // Clean up PDF file
      if (Storage::exists($pdfPath)) {
        Storage::delete($pdfPath);
      }

      return $successCount > 0;

    } catch (\Exception $e) {
      $report->update(['status' => ReportStatus::FAILED]);
      Log::error("Failed to send report", [
        'report_id' => $report->uuid,
        'error' => $e->getMessage()
      ]);
      return false;
    }
  }

  /**
   * Send weekly reports automatically
   */
  public function sendAutomaticWeeklyReports(Collection $reports, array $defaultRecipients): array
  {
    $results = [];

    foreach ($reports as $report) {
      try {
        $success = $this->sendReport($report, $defaultRecipients);
        $results[] = [
          'report_id' => $report->uuid,
          'success' => $success,
          'user_id' => $report->generated_by,
        ];
      } catch (\Exception $e) {
        $results[] = [
          'report_id' => $report->uuid,
          'success' => false,
          'error' => $e->getMessage(),
          'user_id' => $report->generated_by,
        ];
      }
    }

    return $results;
  }

  /**
   * Create a report recipient record
   */
  private function createReportRecipient(Report $report, array $recipientData): ReportRecipient
  {
    return ReportRecipient::create([
      'report_id' => $report->uuid,
      'contact_id' => $recipientData['contact_id'] ?? null,
      'email' => $recipientData['email'],
      'name' => $recipientData['name'],
      'delivery_status' => 'pending',
    ]);
  }

  /**
   * Send email to a specific recipient
   */
  private function sendEmailToRecipient(Report $report, ReportRecipient $recipient, ReportTemplate $template, string $pdfPath): void
  {
    $emailData = $this->prepareEmailData($report, $recipient, $template);

    Mail::send('emails.report', $emailData, function ($message) use ($recipient, $emailData, $pdfPath) {
      $message->to($recipient->email, $recipient->name)
        ->subject($emailData['subject'])
        ->attach(Storage::path($pdfPath), [
          'as' => $emailData['pdf_filename'],
          'mime' => 'application/pdf',
        ]);
    });
  }

  /**
   * Prepare email data for sending
   */
  private function prepareEmailData(Report $report, ReportRecipient $recipient, ReportTemplate $template): array
  {
    $variables = $this->getTemplateVariables($report, $recipient);

    $subject = $this->replaceTemplateVariables($template->getDefaultEmailSubject($variables), $variables);
    $body = $this->replaceTemplateVariables($template->getDefaultEmailBody($variables), $variables);

    return [
      'subject' => $subject,
      'body' => $body,
      'report' => $report,
      'recipient' => $recipient,
      'pdf_filename' => $this->generatePdfFilename($report),
      'variables' => $variables,
    ];
  }

  /**
   * Get template variables for email generation
   */
  private function getTemplateVariables(Report $report, ReportRecipient $recipient): array
  {
    $generator = User::find($report->generated_by);

    return [
      'recipient_name' => $recipient->name,
      'sender_name' => $generator->name ?? 'System',
      'week_number' => $report->week_number,
      'year' => $report->year,
      'month' => $report->month,
      'week_description' => $report->getWeekDescription(),
      'start_date' => $report->start_date->format('M j, Y'),
      'end_date' => $report->end_date->format('M j, Y'),
      'total_projects' => $report->reportItems()->count(),
      'total_tasks' => $report->total_tasks,
      'completed_tasks' => $report->completed_tasks,
      'total_hours' => $this->formatHours($report->total_hours),
      'completion_rate' => $report->total_tasks > 0 ? round(($report->completed_tasks / $report->total_tasks) * 100, 1) . '%' : '0%',
      'report_title' => $report->title,
      'generated_date' => $report->generated_at->format('M j, Y g:i A'),
    ];
  }

  /**
   * Replace template variables in text
   */
  private function replaceTemplateVariables(string $text, array $variables): string
  {
    foreach ($variables as $key => $value) {
      $text = str_replace('{' . $key . '}', $value, $text);
    }

    return $text;
  }

  /**
   * Generate PDF filename for the report
   */
  private function generatePdfFilename(Report $report): string
  {
    $type = ucfirst($report->report_type->value);
    $date = $report->start_date->format('Y-m-d');

    if ($report->report_type->value === 'weekly') {
      return "Weekly_Report_Week_{$report->week_number}_{$report->year}.pdf";
    }

    return "{$type}_Report_{$date}.pdf";
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
   * Get contacts for automatic report sending
   */
  public function getDefaultReportRecipients(): array
  {
    // This could be configured in settings or environment
    // For now, return empty array - should be configured per user/organization
    return [];
  }

  /**
   * Schedule automatic weekly report sending
   */
  public function scheduleWeeklyReports(): void
  {
    // This would typically be called by a scheduled job
    $reportService = app(ReportGenerationService::class);
    $reports = $reportService->generateAutomaticWeeklyReports();

    $defaultRecipients = $this->getDefaultReportRecipients();

    if (!empty($defaultRecipients)) {
      $this->sendAutomaticWeeklyReports($reports, $defaultRecipients);
    }
  }
}
