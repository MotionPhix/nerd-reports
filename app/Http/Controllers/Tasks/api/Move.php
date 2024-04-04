<?php

namespace App\Http\Controllers\Tasks\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;

class Move extends Controller
{
  public function __invoke(Task $task)
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


    return response()->json([

      'toast', [
        'type' => 'success',
        'title' => $toastTitles->random(),
        'message' => 'Task was successfully moved!'
      ]

    ]);

  }
}
