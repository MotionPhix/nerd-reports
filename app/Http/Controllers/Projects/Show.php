<?php

namespace App\Http\Controllers\Projects;

use App\Data\ProjectFullData;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Inertia\Inertia;

class Show extends Controller
{
    public function __invoke(Project $project)
    {
      $project = ProjectFullData::from(
        $project->load([
          'contact.firm',
          'contact.emails',
          'boards.tasks.user',
          'boards.tasks.comments.user',
          'boards.tasks' => function ($query) {
            $query->withCount('comments');
          }
        ])
      );

      return Inertia::render('Projects/Show', [
        'project' => $project // $projectFullData->load(['owner', 'contact.company', 'users', 'boards.tasks' => fn($query) => $query->orderBy('position')])
      ]);

    }
}
