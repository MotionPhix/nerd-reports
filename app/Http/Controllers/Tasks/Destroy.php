<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Destroy extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Task $task)
  {
    DB::transaction(function () use ($task) {

      foreach ($task->comments as $comment) {

        if ($comment->has('files')) {

          foreach ($comment->files as $file) {

            Storage::delete($file->full_url);

            $file->delete();
          }

        }

        $comment->delete();
      }

      $task->delete();

    });

    return redirect()->back();

  }

}
