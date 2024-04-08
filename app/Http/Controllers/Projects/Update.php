<?php

namespace App\Http\Controllers\Projects;

use App\Data\ProjectFullData;
use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class Update extends Controller
{
  public function __invoke(ProjectFullData $projectFullData, Project $project): RedirectResponse
  {
    $validated = $projectFullData->exclude('boards', 'contact')->toArray();

    $validated['due_date'] = Carbon::parse($validated['due_date']);

    if(isset($validated['created_at'])) {
      $validated['created_at'] = Carbon::parse($validated['created_at']);
    }

    $project->update($validated);

    if (isset($validated['file'])) {
      // check if files were uploaded
      if ($validated['file']['documents']) {

        $files = $validated['file']['documents'];

        foreach ($files as $file) {
          $originalFilename = $file->getClientOriginalName();
          $generatedFilename = Str::random(20) . '.' . $file->getClientOriginalExtension();

          $path = $file->storeAs('uploads', $generatedFilename);

          $fileModel = new File();

          $fileModel->project_id = $validated['project_id'];
          $fileModel->original_filename = $originalFilename;
          $fileModel->generated_filename = $generatedFilename;

          $fileModel->path = $path;

          $fileModel->save();
        }
      }
    }

    return redirect()->back();
  }
}
