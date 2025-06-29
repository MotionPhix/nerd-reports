<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportGenerationService;
use App\Services\ReportEmailService;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function __construct(
        protected ReportGenerationService $reportService,
        protected ReportEmailService $emailService
    ) {}

    /**
     * Get reports with pagination and filters
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
            ->paginate($request->per_page ?? 15);

        return response()->json($reports);
    }

    /**
     * Get report statistics
     */
    public function stats(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'total' => $user->generatedReports()->count(),
            'this_month' => $user->generatedReports()
                ->whereBetween('generated_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'pending' => $user->generatedReports()
                ->whereIn('status', ['draft', 'generating'])
                ->count(),
            'sent' => $user->generatedReports()
                ->where('status', 'sent')
                ->count(),
        ]);
    }

    /**
     * Generate weekly report via API
     */
    public function generateWeekly(Request $request)
    {
        $user = Auth::user();
        $week = $request->week ?? now()->weekOfYear;
        $year = $request->year ?? now()->year;

        try {
            $startOfWeek = \Carbon\Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endOfWeek = \Carbon\Carbon::now()->setISODate($year, $week)->endOfWeek();

            $report = $this->reportService->generateWeeklyReport($user, $startOfWeek, $endOfWeek);

            return response()->json([
                'success' => true,
                'report' => $report,
                'message' => 'Weekly report generated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate weekly report: ' . $e->getMessage()
            ], 422);
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
        ]);

        try {
            $success = $this->emailService->sendReport($report, $request->recipients);

            return response()->json([
                'success' => $success,
                'message' => $success ? 'Report sent successfully!' : 'Failed to send report to some recipients.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send report: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update report status
     */
    public function updateStatus(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $request->validate([
            'status' => 'required|in:draft,generating,generated,sending,sent,failed',
        ]);

        $report->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'report' => $report,
            'message' => 'Report status updated successfully!'
        ]);
    }
}
