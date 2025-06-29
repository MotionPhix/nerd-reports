<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\ReportGenerationService;
use App\Services\ReportPdfService;
use App\Services\ReportEmailService;
use App\Models\Report;
use App\Models\Contact;
use App\Models\Firm;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function __construct(
        protected ReportGenerationService $reportService,
        protected ReportPdfService $pdfService,
        protected ReportEmailService $emailService
    ) {}

    /**
     * Display reports index
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $reports = $user->generatedReports()
            ->with(['reportItems'])
            ->when($request->type, function ($query, $type) {
                $query->where('report_type', $type);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderBy('generated_at', 'desc')
            ->paginate(15);

        return Inertia::render('Reports/Index', [
            'reports' => $reports,
            'filters' => $request->only(['type', 'status', 'search']),
            'stats' => [
                'total' => $user->generatedReports()->count(),
                'this_month' => $user->generatedReports()
                    ->whereBetween('generated_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'pending' => $user->generatedReports()
                    ->whereIn('status', ['draft', 'generating'])
                    ->count(),
            ]
        ]);
    }

    /**
     * Show report details
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);

        $report->load(['reportItems.project.contact.firm', 'reportRecipients']);

        return Inertia::render('Reports/Show', [
            'report' => $report,
            'summary' => [
                'total_projects' => $report->reportItems()->count(),
                'completion_rate' => $report->total_tasks > 0 ?
                    round(($report->completed_tasks / $report->total_tasks) * 100, 1) : 0,
                'average_hours_per_project' => $report->reportItems()->count() > 0 ?
                    round($report->total_hours / $report->reportItems()->count(), 2) : 0,
            ]
        ]);
    }

    /**
     * Show create report form
     */
    public function create()
    {
        $user = Auth::user();

        return Inertia::render('Reports/Create', [
            'contacts' => Contact::with('firm')->get(),
            'firms' => Firm::all(),
            'projects' => Project::with('contact.firm')->get(),
            'report_types' => [
                ['value' => 'weekly', 'label' => 'Weekly Report'],
                ['value' => 'custom', 'label' => 'Custom Date Range'],
                ['value' => 'monthly', 'label' => 'Monthly Report'],
                ['value' => 'project_specific', 'label' => 'Project Specific'],
                ['value' => 'client_specific', 'label' => 'Client Specific'],
            ]
        ]);
    }

    /**
     * Generate a new report
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|in:weekly,custom,monthly,project_specific,client_specific',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'contact_id' => 'nullable|exists:contacts,uuid',
            'firm_id' => 'nullable|exists:firms,uuid',
            'project_id' => 'nullable|exists:projects,uuid',
        ]);

        $user = Auth::user();
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        try {
            $options = [
                'title' => $request->title,
                'description' => $request->description,
                'contact_id' => $request->contact_id,
                'firm_id' => $request->firm_id,
                'project_id' => $request->project_id,
            ];

            $report = match ($request->type) {
                'weekly' => $this->reportService->generateWeeklyReport($user, $startDate, $endDate),
                'project_specific' => $this->reportService->generateProjectReport(
                    $user,
                    Project::findOrFail($request->project_id),
                    $startDate,
                    $endDate
                ),
                default => $this->reportService->generateCustomReport($user, $startDate, $endDate, $options),
            };

            return redirect()->route('reports.show', $report)
                ->with('success', 'Report generated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate report: ' . $e->getMessage()]);
        }
    }

    /**
     * Download report as PDF
     */
    public function downloadPdf(Report $report)
    {
        $this->authorize('view', $report);

        try {
            return $this->pdfService->downloadReportPdf($report);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * Preview report as PDF in browser
     */
    public function previewPdf(Report $report)
    {
        $this->authorize('view', $report);

        try {
            return $this->pdfService->streamReportPdf($report);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to preview PDF: ' . $e->getMessage()]);
        }
    }

    /**
     * Send report via email
     */
    public function sendEmail(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'recipients' => 'required|array|min:1',
            'recipients.*.email' => 'required|email',
            'recipients.*.name' => 'required|string',
            'recipients.*.contact_id' => 'nullable|exists:contacts,uuid',
        ]);

        try {
            $success = $this->emailService->sendReport($report, $request->recipients);

            if ($success) {
                return back()->with('success', 'Report sent successfully!');
            } else {
                return back()->withErrors(['error' => 'Failed to send report to some recipients.']);
            }
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to send report: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete a report
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);

        $report->delete();

        return redirect()->route('reports.index')
            ->with('success', 'Report deleted successfully!');
    }

    /**
     * Generate weekly report for current user
     */
    public function generateWeekly(Request $request)
    {
        $user = Auth::user();
        $week = $request->week ?? now()->weekOfYear;
        $year = $request->year ?? now()->year;

        $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
        $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();

        try {
            $report = $this->reportService->generateWeeklyReport($user, $startOfWeek, $endOfWeek);

            return redirect()->route('reports.show', $report)
                ->with('success', 'Weekly report generated successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to generate weekly report: ' . $e->getMessage()]);
        }
    }
}
