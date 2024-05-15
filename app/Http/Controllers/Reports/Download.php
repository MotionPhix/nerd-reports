<?php

namespace App\Http\Controllers\Reports;

use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Download extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {
    $startOfWeek = Carbon::now()->startOfWeek()->startOfDay(); // Start of Monday
    $endOfWeek = Carbon::now()->startOfWeek()->addWeek()->subDay()->setHour(16)->setMinute(30)->setSecond(0);

    $projects = Project::with(['contact.firm', 'boards.tasks'])
      ->whereHas('boards.tasks', function ($query) use ($startOfWeek, $endOfWeek) {

        // $query->where('tasks.created_at', '=', Carbon::yesterday());
        $query->whereBetween('tasks.created_at', [$startOfWeek, $endOfWeek]);
      })
      ->get();

    $reportData = [];

    foreach ($projects as $project) {
      $projectData = [
        'project_id' => $project->pid,
        'project_name' => $project->name,
        'project_contact' => $project->contact,
        'project_week' => Carbon::create($project->created_at)->week,
        'tasks' => []
      ];

      foreach ($project->boards as $board) {
        foreach ($board->tasks as $task) {
          // Filter tasks assigned to the current user
          if ($task->assigned_to === auth()->id()) {
            $projectData['tasks'][] = [
              'task_id' => $task->tid,
              'task_name' => $task->name,
              'task_info' => $task->description,
              'assigned_by' => $task?->assignee?->name,
              'assigned_at' => $task->created_at,
              'actual_date' => Carbon::createFromDate($task->created_at)->format('d M, Y'),
              'status' => ProjectStatus::tryFrom($task->status)->getLabel(),
            ];
          }
        }
      }

      // Get project status
      $projectData['status'] = $this->getProjectStatus($projectData['tasks']);

      $reportData[] = $projectData;
    }

    $report = Pdf::loadView('reports.download', [
      'reportData' => $reportData,
      'weekNumber' => date('W', strtotime($startOfWeek)),
    ]);

    return $report->download(Carbon::now('africa/blantyre')->format('d M, Y h:i:s') . '-Weekly-Report.pdf');

    return view('reports.download', [
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
