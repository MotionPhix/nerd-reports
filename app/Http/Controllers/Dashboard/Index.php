<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke()
  {
    $tasks = Task::count();
    $projects = Project::count();
    $contacts = Contact::count();
    $recentContacts = Contact::latest()->take(5)->get(['id', 'first_name', 'last_name', 'created_at']);
    $recentProjects = Project::latest()->take(5)->get(['name', 'created_at', 'created_by']);

    $upcomingDeadlines = Project::whereNotNull('due_date')
    ->whereDate('due_date', '>=', now())
    ->orderBy('due_date')
    ->take(5)
    ->get(['id', 'name', 'due_date']);

    $projectsByStatus = Project::selectRaw('status, COUNT(*) as count')
    ->groupBy('status')
    ->get()
    ->map(fn($row) => [
        'status' => $row->status,
        'count' => $row->count,
    ]);

    // Get current month data
    $currentMonthData = Project::whereYear('created_at', now()->year)
    ->whereMonth('created_at', now()->month)
    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date')
    ->get();

    // Get previous month data
    $previousMonth = now()->subMonth();
    $previousMonthData = Project::whereYear('created_at', $previousMonth->year)
    ->whereMonth('created_at', $previousMonth->month)
    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date')
    ->get();

    // Prepare the data for charting
    $chartData = [
      'current_month' => $currentMonthData->map(fn($d) => [
        'date' => $d->date,
        'count' => $d->count,
      ])->values(),
      'previous_month' => $previousMonthData->map(fn($d) => [
        'date' => $d->date,
        'count' => $d->count,
      ])->values(),
    ];

    return Inertia::render('dashboard', [

      'dashboardData' => [
        'totalContacts' => $contacts,
        'totalTasks' => $tasks,
        'totalProjects' => $projects,
        'chart_data' => $chartData,
        'recentProjects' => $recentProjects,
        'upcomingDeadlines' => $upcomingDeadlines,
        'recentContacts' => $recentContacts,
        'projectsByStatus' => $projectsByStatus,
      ]

    ]);
  }
}
