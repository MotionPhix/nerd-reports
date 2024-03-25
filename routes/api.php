<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(
    ['prefix' => 'companies'], function () {

    Route::post(
      '/',
      \App\Http\Controllers\Firms\Store::class
    )->name('firms.store');

    Route::get(
      '/{q?}',
      \App\Http\Controllers\Firms\Index::class
    )->name('firms.index');

  });
