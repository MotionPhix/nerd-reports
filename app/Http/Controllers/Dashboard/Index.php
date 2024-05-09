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
  public function __invoke(Request $request)
  {
    $tasks = Task::all();
    $projects = Project::all();
    $contacts = Contact::all();

    // Get current month data
    $currentMonthData = Project::whereYear('created_at', now()->year)
    ->whereMonth('created_at', now()->month)
    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date')
    ->get();

    // Get previous month data
    $previousMonthData = Project::whereYear('created_at', now()->subMonth()->year)
    ->whereMonth('created_at', now()->subMonth()->month)
    ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
    ->groupBy('date')
    ->orderBy('date')
    ->get();

    // Prepare the data for charting
    $chartData = [];

    foreach ($currentMonthData as $data) {
      $chartData['current_month'][$data->date] = $data->count;
    }

    foreach ($previousMonthData as $data) {
      $chartData['previous_month'][$data->date] = $data->count;
    }

    return Inertia::render('Dashboard', [

      'dashboardData' => [
        'totalContacts' => $contacts->count(),
        'totalTasks' => $tasks->count(),
        'totalProjects' => $projects->count(),
        'chart_data' => $chartData,
      ]

    ]);
  }
}
