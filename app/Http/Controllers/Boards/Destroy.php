<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;

class Destroy extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Project $project, Board $board): RedirectResponse
  {
    $board = $board->load('tasks');

    if ($board->tasks->count()) {

      \App\Models\Task::where('board_id', $board->id)->delete();

    }

    $board->delete();

    $toastTitles = collect([
      "Board Deleted",
      "Board Annihilated",
      "Board Eliminated",
      "Board Obliterated",
      "Board Terminated",
      "Board Expunged",
      "Board Eradicated",
      "Board Removed",
      "Board Disintegrated",
      "Board Exterminated",
      "Board Vanished",
      "Board Liquidated"
    ]);

    return redirect()->back()->with('toast', [
      'type' => 'success',
      'title' => $toastTitles->random(),
      'message' => 'Board was permanently deleted.'
    ]);
  }
}
