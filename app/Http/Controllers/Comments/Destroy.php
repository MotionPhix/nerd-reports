<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Notifications\CommentRemoved;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\EC\BaseCurves\KoblitzPrime;

class Destroy extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Comment $comment)
  {
    if (auth()->user()->id === $comment->user_id) {

      if ($comment->has('files')) {

        foreach ($comment->files as $file) {

          Storage::disk('files')->delete($file->file_path);

          $file->delete();
        }

      }

      $task = $comment->task;

      $users = $task->comments()
        ->with('user')
        ->where('user_id', '!=', auth()->user()->id)
        ->get()
        ->pluck('user')->unique('id');

      if (! $users->contains($task->user) && $task->user->id !== auth()->user()->id) {

        $users->push($task->user);

      }

      Notification::send($users, new CommentRemoved(auth()->user()));

      $comment->delete();

      return redirect()->back();

    }

    return redirect()->back()->withErrors([
      'unathorised' => 'You are not the owner of the comment!'
    ]);
  }
}
