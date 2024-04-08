<?php

namespace App\Http\Controllers\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Destroy extends Controller
{
  public function __invoke(string $ids)
  {
    $pidArray = explode(',', $ids);

    DB::transaction(function () use ($pidArray) {

      foreach ($pidArray as $pid) {

        $project = Project::with('files', 'boards.tasks')->where('pid', $pid)->firstOrFail();

        // Delete project files
        if ($project->files->isNotEmpty()) {

          foreach ($project->files as $file) {

            Storage::delete($file->path);

            $file->delete();

          }
        }

        if (isset($project->boards)) {

          $project->boards->each(function ($board) {

            if (isset($board->tasks)) {

              $board->tasks()->delete();

            }

            $board->delete();

          });

        }

        $project->delete();

      }

    });

    $toastTitles = collect([
      "Project Deleted",
      "Mission Accomplished",
      "Project Annihilated",
      "Project Obliterated",
      "Project Expunged",
      "Project Eradicated",
      "Project Disintegrated",
      "Project Exterminated",
      "Project Vanished"
    ]);

    return redirect()->route('projects.index')->with('toast', [
      'type' => 'success',
      'title' => $toastTitles->random(),
      'message' => 'Project was permanently deleted.'
    ]);
  }
}
