<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
  ]);
});

Route::middleware('auth')->group(function () {

  Route::prefix('contacts')->group(function () {
    Route::get(
      '/',
      \App\Http\Controllers\Contacts\Index::class
    )->name('contacts.index');

    Route::get(
      '/c/new',
      \App\Http\Controllers\Contacts\Form::class
    )->name('contacts.create');

    Route::post(
      '/store',
      \App\Http\Controllers\Contacts\Store::class
    )->name('contacts.store');

    Route::get(
      '/s/{contact:cid}',
      \App\Http\Controllers\Contacts\Show::class
    )->name('contacts.show');

    Route::get(
      '/e/{contact:cid}',
      \App\Http\Controllers\Contacts\Form::class
    )->name('contacts.edit');

    Route::patch(
      '/u/{contact:cid}',
      \App\Http\Controllers\Contacts\Update::class
    )->name('contacts.update');

    Route::delete(
      '/d/{ids}',
      \App\Http\Controllers\Contacts\Destroy::class
    )->name('contacts.destroy');

    Route::put(
      '/r/{contact:cid}',
      \App\Http\Controllers\Contacts\Restore::class
    )->name('contacts.restore');
  });

  Route::prefix('projects')->group(function () {
    Route::get(
      '/',
      \App\Http\Controllers\Projects\Index::class
    )->name('projects.index');

    Route::post(
      '/store',
      \App\Http\Controllers\Projects\Store::class
    )->name('projects.store');

    Route::get(
      '/s/{project:pid}',
      \App\Http\Controllers\Projects\Show::class
    )->name('projects.show');

    Route::get(
      '/c/p/{contact:cid?}',
      \App\Http\Controllers\Projects\Form::class
    )->name('projects.create');

    Route::get(
      '/e/{project:pid}',
      \App\Http\Controllers\Projects\Form::class
    )->name('projects.edit');

    Route::patch(
      '/u/{project:pid}',
      \App\Http\Controllers\Projects\Update::class
    )->name('projects.update');

    Route::delete(
      '/d/{ids}',
      \App\Http\Controllers\Projects\Destroy::class
    )->name('projects.destroy');
  });


  Route::prefix('boards')->group(function () {

    Route::post(
      '/s/{project:pid}',
      \App\Http\Controllers\Boards\Store::class
    )->name('boards.store');

    Route::patch(
      '/u/{project}/{board}',
      \App\Http\Controllers\Boards\Update::class
    )->name('boards.update');

    Route::delete(
      'd/{project}/{board}',
      \App\Http\Controllers\Boards\Destroy::class
    )->name('boards.destroy');

  });

  Route::prefix('tasks')->group(function () {

    Route::get(
      '/',
      \App\Http\Controllers\Tasks\Index::class
    )->name('tasks.index');

    Route::post(
      '/',
      \App\Http\Controllers\Tasks\Store::class
    )->name('tasks.store');

    Route::get(
      '/s/{task:tid}/{notification?}',
      \App\Http\Controllers\Tasks\Show::class
    )->name('tasks.show');

    Route::patch(
      '/u/{task}',
      \App\Http\Controllers\Tasks\Update::class
    )->name('tasks.update');

    Route::delete(
      '/d/{task}',
      \App\Http\Controllers\Tasks\Destroy::class
    )->name('tasks.destroy');

    Route::put(
      '/m/{task}',
      \App\Http\Controllers\Tasks\Move::class
    )->name('tasks.move');

  });

  Route::prefix('comments')->group(function () {

    Route::post(
      '/{task}',
      \App\Http\Controllers\Comments\Store::class
    )->name('comments.store');

    Route::patch(
      '/u/{comment}',
      \App\Http\Controllers\Comments\Update::class
    )->name('comments.update');

    Route::delete(
      '/d/{comment}',
      \App\Http\Controllers\Comments\Destroy::class
    )->name('comments.destroy');

  });

  Route::prefix('reports')->group(function () {

    Route::get(
      '/download',
      \App\Http\Controllers\Reports\Download::class
    )->name('reports.download');

    Route::post(
      '/w',
      \App\Http\Controllers\Reports\Report::class
    )->name('reports.compile');

    Route::delete(
      '/d/{report}',
      \App\Http\Controllers\Reports\Destroy::class
    )->name('reports.destroy');

    Route::get(
      '/{user?}',
      \App\Http\Controllers\Reports\Index::class
    )->name('reports.index');

  });

  Route::get('/download/{file:fid}', \App\Http\Controllers\Files\Download::class)->name('file.downloads');

  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::get('/dashboard', \App\Http\Controllers\Dashboard\Index::class)->name('dashboard');

});

require __DIR__ . '/auth.php';
