<?php

namespace App\Http\Controllers\Contacts;

use App\Data\ContactFullData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Inertia\Inertia;

class Show extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Contact $contact)
    {
        $contactsQuery = ContactFullData::from($contact->load('phones', 'emails', 'firm'));

        return Inertia::render('Contacts/Show', [
            'contact' => $contactsQuery,
        ]);

    }
}
