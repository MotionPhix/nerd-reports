<?php

namespace App\Http\Controllers\Boards;

use App\Data\BoardData;
use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Project;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class Store extends Controller
{

  public function __invoke(BoardData $boardData, Project $project): RedirectResponse
  {

    $valid = $boardData->toArray();

    $valid['name'] = Str::ucfirst($valid['name']);

    $valid['project_id'] = $project->id;

    try {

      Board::create($valid);

      $toastTitles = collect([
        'Success!',
        'Well done!',
        'Great job!',
        'Awesome!',
        'Congratulations!',
        'Hooray!',
      ]);

      return redirect()->back()->with('toast', [
        'type' => 'success',
        'title' => $toastTitles->random(),
        'message' => 'Board was successfully created!'
      ]);

    } catch (QueryException $e) {

      if ($e->errorInfo[1] === 1062) {

        return redirect()->back()
          ->withErrors([
            'name' => 'A board with this name already exists in this project.'
          ]);

      }

      throw $e;
    }

  }
}
