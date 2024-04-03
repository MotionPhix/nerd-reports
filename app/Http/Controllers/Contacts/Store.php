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
            $firm = Firm::updateOrCreate(['fid' => $validated['firm']['fid']], $firmFields);

            $contact->firm()->associate($firm)->save();

            if (isset($validated['firm']['address'])) {

                $addressData = $validated['firm']['address'];
    
                if(isset($addressData['id'])) {

                    // If address ID is provided, update existing address
                    $firm->address()->updateOrCreate(['id' => $addressData['id']], $addressData);

                } else {

                    // If no address ID provided, create a new address
                    $firm->address()->create($addressData);

                }

            }
        }

        return redirect(route('contacts.index'));
    }
}
