<?php

namespace App\Http\Controllers\Tasks;

use App\Data\TaskData;
use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Show extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, Task $task, $notification = null)
  {
    if ($notification) {

      $notification = auth()->user()
        ->unreadNotifications
        ->where('id', $notification)
        ->first();

      if ($notification) {
        $notification->markAsRead();
      }

    }

    $task = TaskData::from($task->load(['user', 'comments.user', 'comments.files']))->toArray();

    if ($request->wantsJson()) {

      return $task;

    }

    return Inertia::render('Tasks/Show', [

      'localTask' => $task

    ]);

  }
}
