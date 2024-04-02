<?php

namespace App\Http\Controllers\Tags\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Tags\Tag;

class Destroy extends Controller
{
  public function __invoke(Request $request, Tag $tag)
  {
    $tag->update($request->validated());

    return redirect()->back();
  }
}
