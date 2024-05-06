<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Request $request)
  {

    $notifications = auth()->user()->notifications()->latest()->get();

//    if ($request->query('seen')) {
//
//      $notifications = auth()->user()->notifications()->where('read_at', '!=', null)->latest()->get();
//
//    }

    // Modify user data within each notification
    $notifications->transform(function ($notification) {

      /*$notification->data['user'] = [

        'id' => auth()->id(),

        'name' => auth()->user()->name,

        'email' => auth()->user()->email,

      ];*/

      $data = $notification->data;

      $user = User::where('id', $data['user']['id'])->first();

      $data['user'] = [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'avatar_url' => $user->avatar_url,
      ];

      $data['comment'] = [
        ...$notification->data['comment'],
        'task_tid' => Task::where('id', $data['comment']['task_id'])->first()->tid,
        'created_at' => Carbon::parse($data['comment']['created_at'])->diffForHumans(),
      ];

      $notification->data = $data;

      return $notification;

    });

    if ($request->wantsJson()) return $notifications;

    return Inertia::render('Notifications/Index', [

      'notifications' => $notifications, // auth()->user()->notifications()->latest()->paginate()

    ]);
  }
}
