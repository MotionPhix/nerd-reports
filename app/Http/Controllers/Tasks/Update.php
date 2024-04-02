<?php

namespace App\Http\Controllers\Tasks;

use App\Data\TaskData;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class Update extends Controller
{
  public function __invoke(TaskData $taskData, Task $task): RedirectResponse
  {
    $validated = $taskData->toArray();

    dd($validated);

    $task->update($validated);

    // Dispatch the TaskUpdated event
    // event(new TaskUpdated($task));

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
      'message' => 'Task was successfully updated!'
    ]);
  }
}
