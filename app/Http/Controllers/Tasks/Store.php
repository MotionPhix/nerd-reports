<?php

namespace App\Http\Controllers\Tasks;

use App\Data\TaskData;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class Store extends Controller
{
  public function __invoke(TaskData $taskData): RedirectResponse
  {
    $validated = $taskData->toArray();

    $validated['status'] = 'in_progress';

    $validated['assigned_by'] = auth()->user()->id;

    Task::create($validated);

    $toastTitles = collect([
      'Well done!',
      'Great job!',
      'Awesome!',
      'Congratulations!',
      'Hooray!',
    ]);

    return redirect()->back()->with('toast', [
      'type' => 'success',
      'title' => $toastTitles->random(),
      'message' => 'Task was successfully created!'
    ]);

  }
}
