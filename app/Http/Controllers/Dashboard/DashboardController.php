<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
  public function __construct(
    protected DashboardService $dashboardService
  )
  {
  }

  /**
   * Display the dashboard
   */
  public function index(Request $request)
  {
    $user = Auth::user();

    // Get comprehensive dashboard data
    $dashboardData = $this->dashboardService->getDashboardData($user);

    // Get quick stats for real-time updates
    $quickStats = $this->dashboardService->getQuickStats($user);

    return Inertia::render('dashboard/Index', [
      'dashboardData' => $dashboardData,
      'quickStats' => $quickStats,
      'user' => $user->load(['assignedTasks' => function ($query) {
        $query->whereIn('status', ['todo', 'in_progress'])->limit(5);
      }]),
    ]);
  }

  /**
   * Get dashboard data via API (for real-time updates)
   */
  public function getData(Request $request)
  {
    $user = Auth::user();

    $type = $request->get('type', 'overview');

    return response()->json(match ($type) {
      'overview' => $this->dashboardService->getQuickStats($user),
      'tasks' => $this->dashboardService->getDashboardData($user)['tasks'],
      'projects' => $this->dashboardService->getDashboardData($user)['projects'],
      'interactions' => $this->dashboardService->getDashboardData($user)['interactions'],
      'reports' => $this->dashboardService->getDashboardData($user)['reports'],
      default => $this->dashboardService->getDashboardData($user),
    });
  }

  /**
   * Clear dashboard cache
   */
  public function clearCache(Request $request)
  {
    $user = Auth::user();
    $this->dashboardService->clearDashboardCache($user);

    return response()->json(['message' => 'Cache cleared successfully']);
  }
}
