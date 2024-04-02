<?php

namespace App\Http\Controllers\Tags\Api;

use App\Data\TagData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class Update extends Controller
{
  public function __invoke(TagData $tagData, Contact $contact): JsonResponse
  {
    $firm = $contact->firm()->firstOrFail();

    $firmTags = $firm->tags->toArray();

    $tag = Str::title($tagData->name);

    if (!in_array($tag, $firmTags)) {

      $firm->attachTag($tag, 'App\Models\Firm');

    }

    return response()->json($contact->load('firm.tags:id,name'));
  }
}
