<?php

namespace App\Http\Controllers\Contacts;

use App\Data\ContactFullData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Firm;

class Store extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ContactFullData $contactFullData)
    {
        $validated = ($contactFullData->toArray());

        $contact = Contact::create($validated);

        if (isset($validated['phones'])) {
            $convertedPhones = [];

            foreach ($validated['phones'] as $key => $phone) {
                $convertedPhones[$key]['type'] = $phone['type'];
                $convertedPhones[$key]['is_primary_phone'] = $phone['is_primary_phone'];
                $convertedPhones[$key]['country_code'] = $phone['country_code'];
                $convertedPhones[$key]['number'] = phone($phone['number'], $phone['country_code'])->formatE164();
                $convertedPhones[$key]['formatted'] = phone($phone['number'], $phone['country_code'])->formatInternational();
            }

            $contact->phones()->createMany($convertedPhones);
        }

        if (isset($validated['emails'])) {
            $contact->emails()->createMany($validated['emails']);
        }

        if (isset($validated['firm'])) {

            $firmFields = array_intersect_key($validated['firm'], array_flip(['url', 'slogan']));
            Firm::updateOrCreate(['id' => $validated['firm']['id']], $firmFields);

            if (isset($validated['firm']['address'])) {
                $contact->firm->address()->updateOrCreate(['id' => $validated['firm']['address']['id']], $validated['firm']['address']);
            }
        }

        return redirect(route('contacts.index'));
    }
}
