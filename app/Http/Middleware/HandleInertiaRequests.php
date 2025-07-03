<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
  /**
   * The root template that is loaded on the first page visit.
   *
   * @var string
   */
  protected $rootView = 'app';

  /**
   * Determine the current asset version.
   */
  public function version(Request $request): string|null
  {
    return parent::version($request);
  }

  /**
   * Define the props that are shared by default.
   *
   * @return array<string, mixed>
   */
  public function share(Request $request): array
  {
    return [
      ...parent::share($request),
      'auth' => [
        'user' => fn() => $request->user()
          ? [
            'id' => $request->user()->id,
            'uuid' => $request->user()->uuid,
            'first_name' => $request->user()->first_name,
            'last_name' => $request->user()->last_name,
            'name' => $request->user()->first_name . ' ' . $request->user()->last_name,
            'email' => $request->user()->email,
            'avatar_url' => $request->user()->avatar_url,
          ]
          : null,

        'unreadNotifications' => fn() => $request->user()?->unreadNotifications()->count(),
      ],

      'appName' => env('APP_NAME', 'Rapports'),
    ];
  }
}
