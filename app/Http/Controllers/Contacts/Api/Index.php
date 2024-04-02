<?php

namespace App\Http\Controllers\Contacts\Api;

use App\Data\ContactData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Spatie\LaravelData\DataCollection;

class Index extends Controller
{
  public function __invoke()
  {
    $contactsQuery = ContactData::collect(Contact::all(), DataCollection::class)->include('firm');

    return response()->json([
      'contacts' => $contactsQuery
    ]);
  }
}
