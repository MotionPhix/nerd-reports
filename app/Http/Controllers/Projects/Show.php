<?php

namespace App\Http\Controllers\Projects;

use App\Data\ProjectFullData;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Show extends Controller
{
  public function __invoke(Request $request, Project $project)
  {
    $project = ProjectFullData::from(
      $project->load([
        'contact.firm',
        'contact.emails',
        'boards.tasks' => function ($query) {
          $query->orderBy('position')
            ->with(['user', 'comments.user', 'comments.files'])
            ->withCount(['comments', 'files']);
        },
      ])
    );

    if ($request->wantsJson()) {
      return $project;
    }

    return Inertia::render('Projects/Show', [
      'project' => $project
    ]);

  }
}
