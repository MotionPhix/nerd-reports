<?php

namespace App\Http\Controllers\Comments;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
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

      $comment->delete();

      return redirect()->back();

    }

    return redirect()->back()->withErrors([
      'unathorised' => 'You are not the owner of the comment!'
    ]);
  }
}
