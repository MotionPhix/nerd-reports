<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Task;

class Destroy extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Task $task)
    {
      $task->delete();

      return redirect()->back();
    }
}
