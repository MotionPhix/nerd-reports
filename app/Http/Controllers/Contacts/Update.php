<?php

namespace App\Http\Controllers\Contacts;

use App\Data\ContactFullData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Email;
use App\Models\Firm;
use App\Models\Phone;

class Update extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ContactFullData $contactFullData, Contact $contact)
    {
        $validated = $contactFullData->toArray();

        $contact->update($validated);

        if (isset($validated['phones'])) {

            $phoneIds = [];

            foreach ($validated['phones'] as $phone) {

                $phone['number'] = phone($phone['number'], $phone['country_code'])->formatE164();
                $phone['formatted'] = phone($phone['number'], $phone['country_code'])->formatInternational();

                if (isset($phone['id'])) {

                    $phone_exists = Phone::find($phone['id']);

                    if (
                        $phone_exists &&
                        (
                            ($phone['number'] !== $phone_exists->number) ||
                            ($phone_exists->country_code !== $phone['country_code']) ||
                            (!! $phone['is_primary_phone'] !== !! $phone_exists->is_primary_phone)
                        )
                    ) {

                        $phone_exists->update($phone);

                    }

                    $phoneIds[] = $phone['id'];
                } else {

                    $new_phone = new Phone($phone);

                    $contact->phones()->save($new_phone);

                    $phoneIds[] = $new_phone->id;
                }
            }

            // Remove any phone numbers that are not in $phoneIds.
            $contact->phones()->whereNotIn('id', $phoneIds)->delete();
        }

        if (isset($validated['emails'])) {

            $emailIds = [];

            foreach ($validated['emails'] as $email) {
                if (isset($email['id'])) {
                    $email_exists = Email::find($email['id']);

                    if ($email_exists && $email['email'] !== $email_exists->email) {
                        $email_exists->update($email);
                    }

                    $emailIds[] = $email['id'];
                } else {

                    $new_email = new Email($email);
                    $contact->emails()->save($new_email);

                    $emailIds[] = $new_email->id;
                }
            }

            $contact->emails()->whereNotIn('id', $emailIds)->delete();
        }

        /*if (isset($validated['firm'])) {

            $addressIds = [];

            foreach ($request->addresses as $key => $address) {
                if (isset($address['id'])) {
                    $address_exists = Address::find($address['id']);

                    if ($address_exists) {
                        if (
                            $address['city'] !== $address_exists->city ||
                            $address['state'] !== $address_exists->state ||
                            $address['street'] !== $address_exists->street ||
                            $address['country'] !== $address_exists->country
                        ) {
                            $address_exists->update($address);
                        }
                    }

                    $addressIds[] = $address['id'];
                } else {

                    $new_address = new Address($address);
                    $contact->addresses()->save($new_address);

                    $addressIds[] = $new_address->id;
                }
            }

            $contact->addresses()->whereNotIn('id', $addressIds)->delete();
        }*/

        if (isset($validated['firm'])) {

            $firmData = array_intersect_key($validated['firm'], array_flip(['url', 'slogan']));

            $firm = Firm::updateOrCreate(['id' => $validated['firm']['id']], $firmData);

            // update or create address for contact with firm id
            if (isset($validated['firm']['address'])) {
                $firm->address()->updateOrCreate(['id' => $firm->address?->id], [$firm->address]);
            }

        }

        $title = [
            'Great!',
            'Awesome',
            'That\'s about right!'
        ][rand(0, 2)];

        return redirect(route('contacts.show', $contact->cid))
            ->with('toast', [
                'type' => $title,
                'message' => 'Contact was updated successfully!'
            ]);
    }
}
