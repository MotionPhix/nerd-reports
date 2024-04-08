<?php

namespace App\Http\Controllers\Boards;

use App\Data\BoardData;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Support\Str;

class Update extends Controller
{

  public function __invoke(BoardData $boardData, Project $project, Board $board)
  {
    $validated = $boardData->toArray();

    $board->name = Str::ucfirst($validated['name']);

    $board->save();

    return redirect()->back()->with('toast', [
      'type' => 'success',
      'title' => 'Success',
      'message' => 'Board was updated.'
    ]);

  }

}
