<?php

namespace App\Http\Controllers\Comments;

use App\Data\CommentData;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\File;
use App\Models\Task;
use App\Notifications\AddedCommentNotification;
use App\Notifications\CommentAdded;
use Illuminate\Validation\Rule;

class Store extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(CommentData $commentData, Task $task)
    {
      $validated = request()->validate([
        'files.*' => Rule::exists('files', 'id')->where(function ($q) {
          $q->where('user_id', auth()->user()->id);
        })
      ]);

      $comment = new Comment();

      $comment->body = $commentData->body;

      $comment->user_id = auth()->user()->id;

      $addedComment = $task->comments()->save($comment);

      File::find($validated)->each->update([
        'model_id' => $comment->id,
        'model_type' => Comment::class,
      ]);

      if (!! $addedComment->id) {
        $task->user->notify(new CommentAdded(auth()->user(), $addedComment));
      }

      return redirect()->back();
    }
}
