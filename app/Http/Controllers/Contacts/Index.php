<?php

namespace App\Http\Controllers\Contacts;

use App\Data\ContactData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $contactsQuery = ContactData::collect(Contact::all());

        /*if ($request->filter) {
            if ($request->filter === 'favourites') {
                $contactsQuery->where('is_favorite', true);
            } elseif ($request->filter === 'deleted') {
                $contactsQuery->onlyTrashed()
                    ->select('first_name', 'last_name', 'deleted_at', 'id', 'cid');
            }
        }*/

        /*if ($request->wantsJson()) {
            $contacts = $contactsQuery->orderBy('first_name')
                ->get(['id', 'cid', 'first_name', 'last_name'])
                ->transform(function ($contact) {
                    return [
                        'label' => $contact->first_name . ' ' . $contact->last_name,
                        'value' => $contact->cid,
                    ];
                });

            return response()->json($contacts);
        }*/

        /*$contacts = $contactsQuery->orderBy('first_name')->get()->groupBy(fn ($contact) => $contact->first_name[0]);

        $contactsArray = $contacts->toArray();*/

        return Inertia::render('Contacts/Index', [
            'contacts' => $contactsQuery
        ]);
    }
}
