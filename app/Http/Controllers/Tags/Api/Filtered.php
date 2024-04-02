<?php

namespace App\Http\Controllers\Tags\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Inertia\Inertia;

class Filtered extends Controller
{
  public function __invoke($filter)
  {
    $contacts = Contact::orderBy('first_name')
      ->withAnyTags([$filter], 'App\Models\Firm')->get()
      ->groupBy(fn ($contact) => $contact->first_name[0])
      ->toArray();

    return Inertia::render('Contacts/Index', [
      'baseGroup' => $contacts
    ]);
  }
}
