<?php

namespace App\Http\Controllers\Tags\Api;

use App\Data\TagData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Spatie\Tags\Tag;

class Store extends Controller
{
  public function __invoke(TagData $tagData, Contact $contact): JsonResponse
  {
    $validatedTag = $tagData->toArray();

    $tag = Str::title($validatedTag['name']);

    $tag = Tag::findOrCreate($tag, 'App\Models\Firm');

    $firm = $contact->firm()->firstOrFail();

    $firm->attachTag($tag);

    return response()->json($tag);
  }
}
