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

    return Inertia::render('Dashboard', [

      'dashboardData' => [
        'totalContacts' => $contacts->count(),
        'totalTasks' => $tasks->count(),
        'totalProjects' => $projects->count(),
      ]

    ]);
  }
}
