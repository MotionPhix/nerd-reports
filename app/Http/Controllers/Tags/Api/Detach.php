<?php

namespace App\Http\Controllers\Tags\Api;

use App\Data\TagData;
use App\Http\Controllers\Controller;
use App\Models\Contact;

class Detach extends Controller
{
  public function __invoke(TagData $tagData, Contact $contact)
  {
    $firm = $contact->firm()->firstOrFail();

    $firm->detachTag($tagData->name, 'App\Models\Firm');

    return response()->json($contact);
  }
}
