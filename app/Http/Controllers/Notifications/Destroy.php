<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class Destroy extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
      $notification = auth()->user()
        ->notifications()
        ->where('id', $id)
        ->first();

      if ($notification) {

        $notification->delete();

      }

      if ($request->wantsJson()) {

        return response([
          'message' => 'Ok'
        ]);

      }

      return back();

    }
}
