<?php

namespace App\Http\Controllers\Projects;

use App\Data\ProjectFullData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\File;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Store extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(ProjectFullData $projectFullData)
  {
    $validated = ($projectFullData->toArray());

    $validated['due_date'] = Carbon::createFromDate($validated['due_date'])->timezone('Africa/Blantyre');

    $validated['contact_id'] = Contact::where('cid', $validated['contact_id'])->first()->id;

    $validated['created_by'] = auth()->user()->id;

    $project = Project::create($validated);

    // check if files were uploaded
    if (isset($validated['file']['documents'])) {

      $files = $validated['file']['documents'];

      foreach ($files as $file) {
        $originalFilename = $file->getClientOriginalName();
        $generatedFilename = $file->hashname . '.' . $file->getExtension();

        $path = $file->storeAs('public/uploads', $generatedFilename);

        $fileModel = new File();
        $fileModel->project_id = $project->id;
        $fileModel->user_id = auth()->user()->id;
        $fileModel->original_filename = $originalFilename;
        $fileModel->generated_filename = $generatedFilename;
        $fileModel->path = $path;

        $fileModel->save();
      }
    }

    $toastTitles = collect([
      'Well done!',
      'Great job!',
      'Awesome!',
      'Congratulations!',
      'Hooray!',
    ]);

    return redirect()->route('projects.index')->with('toast', [
      'type' => 'success',
      'title' => $toastTitles->random(),
      'message' => 'Project was successfully created!'
    ]);
  }
}
