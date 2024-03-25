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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
