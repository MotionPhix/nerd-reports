<?php

namespace App\Http\Controllers\Reports;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $startOfWeek = Carbon::now()->startOfWeek()->startOfDay(); // Start of Monday
    $endOfWeek = Carbon::now()->startOfWeek()->addWeek()->subDay()->setHour(16)->setMinute(30)->setSecond(0); // Friday, 4:30 PM

    // Get projects with associated tasks within the weekly range
    $projects = Project::with(['contact.firm', 'boards.tasks' => function ($query) use ($startOfWeek, $endOfWeek) {

      $startOfWeekend = Carbon::now()->startOfWeek()->addDays(5)->startOfDay(); // Start of Saturday
      $endOfWeekend = Carbon::now()->startOfWeek()->addDays(6)->endOfDay(); // End of Sunday

      // $query->whereBetween('created_at', [$startOfWeek, $endOfWeek])
      // $query->whereBetween('created_at', [$startOfWeekend, $endOfWeekend])
      // $query->whereBetween('created_at', [Carbon::yesterday(), Carbon::today()]);
      //   ->selectRaw('DATE(created_at) as date, COUNT(*) as task_count')
      //   ->groupBy('date')
      //   ->orderBy('date', 'asc');

    }])->get();

    $reportData = [];

    foreach ($projects as $project) {
      $projectData = [
        'project_id' => $project->pid,
        'project_name' => $project->name,
        'project_contact' => $project->contact,
        'tasks' => []
      ];

      foreach ($project->boards as $board) {
        foreach ($board->tasks as $task) {
          // Filter tasks assigned to the current user
          if ($task->assigned_to === auth()->id()) {
            $projectData['tasks'][] = [
              'task_id' => $task->tid,
              'task_name' => $task->name,
              'assigned_by' => $task?->assignee?->name,
              'assigned_at' => $task->created_at,
              'status' => ProjectStatus::tryFrom($task->status)->getLabel(),
            ];
          }
        }
      }

      // Determin project status
      $projectData['status'] = $this->getProjectStatus($projectData['tasks']);

      $reportData[] = $projectData;
    }

    return Inertia::render('Reports/Index', [

      'reportData' => $reportData,
      'weekNumber' => date('W', strtotime($startOfWeek)),

    ]);
  }

  private function getProjectStatus($tasks)
  {
    $totalTasks = count($tasks);

    $completedTasks = collect($tasks)->where('status', 'done')->count();
    $canceledTasks = collect($tasks)->where('status', 'cancelled')->count();

    if (!!$totalTasks) {

      if ($completedTasks === $totalTasks) {

        return 'completed';
      } elseif ($completedTasks > 0 || $canceledTasks > 0) {

        return 'in progress';
      } else {

        return 'cancelled';
      }
    }

    return 'not started';
  }
}
