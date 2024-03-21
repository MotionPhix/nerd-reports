<?php

namespace App\Http\Controllers\Contacts;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Index extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = auth()->user();

        $contactsQuery = $user->contacts();

        if ($request->filter) {
            if ($request->filter === 'favourites') {
                $contactsQuery->where('is_favorite', true);
            } elseif ($request->filter === 'deleted') {
                $contactsQuery->onlyTrashed()
                    ->select('first_name', 'last_name', 'deleted_at', 'id', 'cid');
            }
        }

        if ($request->wantsJson()) {
            $contacts = $contactsQuery->orderBy('first_name')
                ->get(['id', 'cid', 'first_name', 'last_name'])
                ->transform(function ($contact) {
                    return [
                        'label' => $contact->first_name . ' ' . $contact->last_name,
                        'value' => $contact->cid,
                    ];
                });

            return response()->json($contacts);
        }

        $contacts = $contactsQuery->orderBy('first_name')->get()->groupBy(fn ($contact) => $contact->first_name[0]);

        $contactsArray = $contacts->toArray();

        return Inertia::render('Contacts/Index', [
            'baseGroup' => $contactsArray
        ]);
    }
}
