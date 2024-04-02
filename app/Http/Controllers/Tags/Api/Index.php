<?php

namespace App\Http\Controllers\Tags\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Spatie\Tags\Tag;

class Index extends Controller
{
  public function __invoke(): JsonResponse
  {
    // $tagIds = \DB::table('tags')
    //   ->distinct()
    //   ->select('tag_id')
    //   ->where('tags.type', 'App\Models\Contact')
    //   ->join('taggables', 'tags.id', '=', 'taggables.tag_id')
    //   ->get()
    //   ->unique()
    //   ->pluck('tag_id');

    // $tags = Tag::whereIn('id', $tagIds)->get()
    //   ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
    //   ->map(function ($tag) {
    //     return [
    //       'label' => $tag->name,
    //       'value' => $tag->id,
    //     ];
    //   })->values();

    $tags = Tag::get()
      ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
      ->map(function ($tag) {
        return [
          'label' => $tag->name,
          'value' => $tag->id,
        ];
      })
      ->values();

    return response()->json($tags);
  }
}
