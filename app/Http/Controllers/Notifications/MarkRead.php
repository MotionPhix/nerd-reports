<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MarkRead extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request, $id)
  {
    $notification = auth()->user()
      ->unreadNotifications
      ->where('id', $id)
      ->first();

    if ($notification) {
      $notification->markAsRead();
    }

    if ($request->wantsJson()) {

      return response([
        'message' => 'Ok'
      ]);

    }

    return back();
  }
}
