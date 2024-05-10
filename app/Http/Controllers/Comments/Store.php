<?php

namespace App\Http\Controllers\Comments;

use App\Data\CommentData;
use App\Events\TaskCommentAdded;
use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\File;
use App\Models\Task;
use App\Notifications\CommentAdded;
use Illuminate\Support\Facades\Notification;
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

    $comment = $task->comments()->save($comment);

    File::find($validated)->each->update([
      'model_id' => $comment->id,
      'model_type' => Comment::class,
    ]);

    $users = $task->comments()
      ->with('user')
      ->where('user_id', '!=', auth()->user()->id)
      ->get()
      ->pluck('user')->unique('id');

    if (! $users->contains($task->user) && $task->user->id !== auth()->user()->id) {

      $users->push($task->user);

    }

    Notification::send($users, new CommentAdded(auth()->user(), $comment));

    // $task->user->notify(new CommentAdded(auth()->user(), $comment));

    // foreach ($users as $user) {

      // broadcast(new TaskCommentAdded(auth()->user(), $comment))->toOthers();

    // }

    return redirect()->back();
  }
}
