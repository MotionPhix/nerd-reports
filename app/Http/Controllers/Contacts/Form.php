<?php

namespace App\Http\Controllers\Contacts;

use App\Data\ContactFullData;
use App\Data\FirmData;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class Form extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Contact $contact = null)
    {
        if (!$contact) {
            $contact = new ContactFullData([
                'first_name' => '',
                'last_name' => '',
                'firm' => new FirmData(null),
            ]);
        }

        $contact->load('phones', 'emails', 'addresses', 'companies');
        $company = $contact->lastCompany;

        // Ensure $company is an empty array if it's null
        $companyData = $company
            ? [
                'id' => [
                    'label' => $company->name,
                    'value' => $company->id,
                ],
                'name' => $company->name,
                'slogan' => $company->slogan,
                'address' => $company->address,
                'url' => $company->url,
                'department' => $company->pivot->department,
                'job_title' => $company->pivot->job_title,
            ]
            : [
                'id' => [
                    'label' => '',
                    'value' => ''
                ],
                'name' => '',
                'slogan' => '',
                'address' => '',
                'url' => '',
                'department' => '',
                'job_title' => '',
            ];

        return Inertia::render('Contacts/Form', [
            'contact' => [
                'id' => $contact->id,
                'cid' => $contact->cid,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'nickname' => $contact->nickname,
                'middle_name' => $contact->middle_name,
                'title' => $contact->title,
                'bio' => $contact->bio,
                'emails' => $contact->emails,
                'phones' => $contact->phones,
                'addresses' => $contact->addresses,
                'company' => $companyData,
            ],
        ]);
    }
}
