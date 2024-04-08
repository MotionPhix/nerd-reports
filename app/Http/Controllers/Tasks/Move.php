<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;

class Move extends Controller
{
  public function __invoke(Task $task): RedirectResponse
  {
    $board = \App\Models\Board::find(request()->board_id);

    $task->position = round(request()->position, 5);
    $task->board_id = $board->id;

    $task->save();

    $toastTitles = collect([
      'Well done!',
      'Great job!',
      'Awesome!',
      'Congratulations!',
      'Hooray!',
    ]);


    return redirect()->back()->with([

      'toast', [
        'type' => 'success',
        'title' => $toastTitles->random(),
        'message' => 'Task was successfully moved!'
      ]

    ]);

  }
}
