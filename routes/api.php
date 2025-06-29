<?php

use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\TasksController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

  // Enhanced API Routes for Reports
  Route::prefix('reports')->name('api.reports.')->group(function () {
    Route::get('/', [ReportsController::class, 'index'])->name('index');
    Route::get('/stats', [ReportsController::class, 'stats'])->name('stats');
    Route::post('/generate-weekly', [ReportsController::class, 'generateWeekly'])->name('generate-weekly');
    Route::post('/{report}/send-email', [ReportsController::class, 'sendEmail'])->name('send-email');
    Route::patch('/{report}/status', [ReportsController::class, 'updateStatus'])->name('update-status');
  });

  // Enhanced API Routes for Tasks
  Route::prefix('tasks')->name('api.tasks.')->group(function () {
    Route::get('/', [TasksController::class, 'index'])->name('index');
    Route::get('/stats', [TasksController::class, 'stats'])->name('stats');
    Route::get('/overdue', [TasksController::class, 'overdue'])->name('overdue');
    Route::post('/{task}/start', [TasksController::class, 'start'])->name('start');
    Route::post('/{task}/complete', [TasksController::class, 'complete'])->name('complete');
    Route::post('/{task}/log-time', [TasksController::class, 'logTime'])->name('log-time');
    Route::post('/{task}/move', [TasksController::class, 'move'])->name('move');
    Route::patch('/{task}/status', [TasksController::class, 'updateStatus'])->name('update-status');
    Route::post('/update-positions', [TasksController::class, 'updatePositions'])->name('update-positions');
  });

  // dashboard API
  Route::prefix('dashboard')->name('api.dashboard.')->group(function () {
    Route::get('/data', [\App\Http\Controllers\Dashboard\DashboardController::class, 'getData'])->name('data');
    Route::post('/clear-cache', [\App\Http\Controllers\Dashboard\DashboardController::class, 'clearCache'])->name('clear-cache');
  });

  // Interactions API
  Route::prefix('interactions')->name('api.interactions.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Interactions\InteractionController::class, 'index'])->name('index');
    Route::get('/stats', [\App\Http\Controllers\Interactions\InteractionController::class, 'stats'])->name('stats');
    Route::get('/contact/{contact}', [\App\Http\Controllers\Interactions\InteractionController::class, 'forContact'])->name('for-contact');
    Route::get('/project/{project}', [\App\Http\Controllers\Interactions\InteractionController::class, 'forProject'])->name('for-project');
  });

  // Projects API
  Route::prefix('projects')->name('api.projects.')->group(function () {
    Route::get('/{project}/stats', [\App\Http\Controllers\Projects\ProjectController::class, 'stats'])->name('stats');
    Route::get('/{project}/timeline', [\App\Http\Controllers\Projects\ProjectController::class, 'timeline'])->name('timeline');
  });

  // Existing API Routes (keeping your current structure)
  Route::get('/get-files', \App\Http\Controllers\Api\Comments\Show::class)->name('files.load');

  Route::get('/users', function () {
    $users = \App\Models\User::select(['id', 'first_name', 'last_name'])->get();
    $formattedUsers = $users->map(function ($user) {
      return [
        'value' => $user->id,
        'label' => "{$user->first_name} {$user->last_name}"
      ];
    });
    return response()->json(['users' => $formattedUsers]);
  });

  Route::group(['prefix' => 'companies'], function () {
    Route::post('/', \App\Http\Controllers\Firms\Store::class)->name('api.firms.store');
    Route::get('/{q?}', \App\Http\Controllers\Firms\Index::class)->name('api.firms.index');
  });

  Route::group(['prefix' => 'contacts'], function () {
    Route::get('/', \App\Http\Controllers\Api\Contacts\Index::class)->name('api.contacts.index');
  });

  Route::group(['prefix' => 'tags'], function () {
    Route::get('/', \App\Http\Controllers\Api\Tags\Index::class)->name('tags.index');
    Route::post('/{contact:cid}', \App\Http\Controllers\Api\Tags\Store::class)->name('tags.store');
    Route::patch('/{contact:cid}', \App\Http\Controllers\Api\Tags\Detach::class)->name('tags.detach');
    Route::put('/{contact:cid}', \App\Http\Controllers\Api\Tags\Update::class)->name('tags.update');
    Route::delete('delete/{tag:name}', \App\Http\Controllers\Api\Tags\Destroy::class)->name('tags.destroy');
    Route::get('/{filter}', \App\Http\Controllers\Api\Tags\Filtered::class)->name('tags.filter');
  });

  Route::prefix('notifications')->group(function () {
    Route::get('/', \App\Http\Controllers\Notifications\Index::class)->name('notifications.index');
    Route::patch('/r/{id}', \App\Http\Controllers\Notifications\MarkRead::class)->name('notifications.read');
    Route::delete('/d/{id}', \App\Http\Controllers\Notifications\Destroy::class)->name('notifications.destroy');
  });
});
