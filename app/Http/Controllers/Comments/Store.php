<?php

namespace App\Http\Controllers\Comments;

use App\Data\CommentData;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Task;

class Store extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CommentData $commentData, Task $task)
    {
      $comment = new Comment();

      $comment->body = $commentData->body;

      $comment->user_id = auth()->user()->id;

      $task->comments()->save($comment);

      return redirect()->back();
    }
}
