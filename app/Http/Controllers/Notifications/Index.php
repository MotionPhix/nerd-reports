<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Data\UserData;
use Carbon\Carbon;
use Inertia\Inertia;

class Index extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke()
  {
    $notifications = auth()->user()->notifications()->latest()->paginate();

    // Modify user data within each notification
    $notifications->transform(function ($notification) {

      /*$notification->data['user'] = [

        'id' => auth()->id(),

        'name' => auth()->user()->name,

        'email' => auth()->user()->email,

      ];*/

      $data = $notification->data;

      $data['user'] = [
        'id' => auth()->id(),
        'name' => auth()->user()->name,
        'email' => auth()->user()->email,
        'avatar_url' => auth()->user()->avatar_url,
      ];

      $data['comment'] = [
        ...$notification->data['comment'],
        'created_at' => Carbon::parse($data['comment']['created_at'])->diffForHumans(),
      ];

      $notification->data = $data;

      return $notification;

    });

    return Inertia::render('Notifications/Index', [

      'notifications' => $notifications, // auth()->user()->notifications()->latest()->paginate()

    ]);
  }
}
