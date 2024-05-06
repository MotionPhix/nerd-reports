<?php

use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

  Route::get(
    '/get-files',
    \App\Http\Controllers\Comments\Api\Show::class
  )->name('files.load');

  Route::get('/users', function () {

    $users = \App\Models\User::select(['id', 'first_name', 'last_name'])->get();

    $formattedUsers = $users->map(function ($user) {

      return [

        'value' => $user->id,

        'label' => "{$user->first_name} {$user->last_name}"

      ];

    });

    return response()->json([

      'users' => $formattedUsers

    ]);

  });

  Route::group(
    ['prefix' => 'companies'],
    function () {

      Route::post(
        '/',
        \App\Http\Controllers\Firms\Store::class
      )->name('api.firms.store');

      Route::get(
        '/{q?}',
        \App\Http\Controllers\Firms\Index::class
      )->name('api.firms.index');
    }
  );

  Route::group(
    ['prefix' => 'contacts'],
    function () {

      Route::get(
        '/',
        \App\Http\Controllers\Contacts\Api\Index::class
      )->name('api.contacts.index');
    }
  );

  Route::group(
    ['prefix' => 'tasks'],
    function () {

      Route::put(
        '/m/{task}',
        \App\Http\Controllers\Tasks\Api\Move::class
      )->name('tasks.move');

    }
  );

  Route::group(
    ['prefix' => 'files'],
    function () {

      Route::post(
        '/u/{task?}',
        \App\Http\Controllers\Files\Api\Store::class
      )->name('files.upload');

      Route::delete(
        '/d/{file}',
        \App\Http\Controllers\Files\Api\Destroy::class
      )->name('files.destroy');

    }
  );

  Route::group(
    ['prefix' => 'tags'],
    function () {

      Route::get(
        '/',
        \App\Http\Controllers\Tags\Api\Index::class
      )->name('tags.index');

      Route::post(
        '/{contact:cid}',
        \App\Http\Controllers\Tags\Api\Store::class
      )->name('tags.store');

      Route::patch(
        '/{contact:cid}',
        \App\Http\Controllers\Tags\Api\Detach::class
      )->name('tags.detach');

      Route::put(
        '/{contact:cid}',
        \App\Http\Controllers\Tags\Api\Update::class
      )->name('tags.update');

      Route::delete(
        'delete/{tag:name}',
        \App\Http\Controllers\Tags\Api\Destroy::class
      )->name('tags.destroy');

      Route::get(
        '/{filter}',
        \App\Http\Controllers\Tags\Api\Filtered::class
      )->name('tags.filter');
    }
  );

  Route::prefix('notifications')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Notifications\Index::class
    )->name('notifications.index');

    Route::patch(
      '/r/{id}', \App\Http\Controllers\Notifications\MarkRead::class
    )->name('notifications.read');

    Route::delete(
      '/d/{id}',
      \App\Http\Controllers\Notifications\Destroy::class
    )->name('notifications.destroy');

  });

});

