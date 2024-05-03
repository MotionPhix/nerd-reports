<?php

namespace App\Http\Controllers\Contacts;

use App\Data\AddressData;
use App\Data\ContactFullData;
use App\Data\EmailData;
use App\Data\FirmData;
use App\Data\PhoneData;
use App\Enums\AddressType;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Inertia\Inertia;

class Form extends Controller
{
  /**
   * Handle the incoming request.
   */
  public function __invoke(Contact $contact = null)
  {
    if (!$contact) {
      $contact = ContactFullData::from([
        'first_name' => '',
        'last_name' => '',
        'phones' => [
          PhoneData::from([
            'type' => 'mobile',
            'number' => '',
            'is_primary_phone' => false,
          ]),
        ],
        'emails' => [
          EmailData::from([
            'email' => '',
            'is_primary_email' => false
          ]),
        ],
        'firm' => FirmData::from([
          'name' => '',
          'address' => AddressData::from([
            'type' => 'work',
            'street' => '',
            'city' => '',
          ])
        ]),
      ]);
    } else {
      $contact = ContactFullData::from($contact->load('phones', 'emails', 'firm.address'));

      if (!$contact->firm?->id) {

        $contact->firm = FirmData::from([

          'slogan' => '',

          'address' => AddressData::from([
            'type' => AddressType::Work,
            'street' => '',
            'city' => ''
          ]),

          'url' => '',

          'name' => '',

        ]);
      }
    }

    return Inertia::render('Contacts/Form', [

      'contact' => $contact

    ]);
  }
}
