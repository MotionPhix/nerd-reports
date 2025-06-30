<?php

namespace App\Http\Controllers\Contacts;

use App\Http\Controllers\Controller;
use App\Services\ContactService;
use App\Models\Contact;
use App\Models\Firm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
  protected ContactService $contactService;

  public function __construct(ContactService $contactService)
  {
    $this->contactService = $contactService;
  }

  /**
   * Display a listing of contacts
   */
  public function index(Request $request): Response
  {
    $filters = $request->only(['search', 'firm_id', 'tags', 'sort_by', 'sort_order']);
    $perPage = $request->get('per_page', 15);

    $contacts = $this->contactService->getPaginatedContacts($filters, $perPage);
    $stats = $this->contactService->getContactStats();

    // Get firms for filter dropdown
    $firms = Firm::select('uuid', 'name')
      ->orderBy('name')
      ->get();

    // Get available tags for filter
    $availableTags = \Spatie\Tags\Tag::where('type', 'contact')
      ->pluck('name')
      ->toArray();

    return Inertia::render('contacts/Index', [
      'contacts' => $contacts,
      'stats' => $stats,
      'firms' => $firms,
      'availableTags' => $availableTags,
      'filters' => $filters,
    ]);
  }

  /**
   * Show the form for creating a new contact
   */
  public function create(): Response
  {
    $firms = Firm::select('uuid', 'name')
      ->orderBy('name')
      ->get();

    return Inertia::render('contacts/Create', [
      'firms' => $firms,
    ]);
  }

  /**
   * Store a newly created contact
   */
  public function store(Request $request): RedirectResponse
  {
    $validator = Validator::make($request->all(), [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'nickname' => 'nullable|string|max:255',
      'job_title' => 'nullable|string|max:255',
      'title' => 'nullable|string|max:255',
      'bio' => 'nullable|string',
      'firm_id' => 'nullable|exists:firms,uuid',
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|phone:AUTO,MW,ZA,ZW',
      'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:255',
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    try {
      $contact = $this->contactService->createContact($request->all());

      return redirect()->route('contacts.show', $contact->uuid)
        ->with('success', 'Contact created successfully.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to create contact: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * Display the specified contact
   */
  public function show(string $uuid): Response
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    $activity = $this->contactService->getContactActivity($contact);

    return Inertia::render('contacts/Show', [
      'contact' => $contact->load(['firm', 'tags', 'emails', 'phones', 'interactions']),
      'activity' => $activity,
    ]);
  }

  /**
   * Show the form for editing the specified contact
   */
  public function edit(string $uuid): Response
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    $firms = Firm::select('uuid', 'name')
      ->orderBy('name')
      ->get();

    return Inertia::render('contacts/Edit', [
      'contact' => $contact->load(['firm', 'tags', 'emails', 'phones']),
      'firms' => $firms,
    ]);
  }

  /**
   * Update the specified contact
   */
  public function update(Request $request, string $uuid): RedirectResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    $validator = Validator::make($request->all(), [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'middle_name' => 'nullable|string|max:255',
      'nickname' => 'nullable|string|max:255',
      'job_title' => 'nullable|string|max:255',
      'title' => 'nullable|string|max:255',
      'bio' => 'nullable|string',
      'firm_id' => 'nullable|exists:firms,uuid',
      'email' => 'nullable|email|max:255',
      'phone' => 'nullable|phone:AUTO,MW,ZA,ZW',
      'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
      'tags' => 'nullable|array',
      'tags.*' => 'string|max:255',
    ]);

    if ($validator->fails()) {
      return redirect()->back()
        ->withErrors($validator)
        ->withInput();
    }

    try {
      $this->contactService->updateContact($contact, $request->all());

      return redirect()->route('contacts.show', $contact->uuid)
        ->with('success', 'Contact updated successfully.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to update contact: ' . $e->getMessage())
        ->withInput();
    }
  }

  /**
   * Remove the specified contact
   */
  public function destroy(string $uuid): RedirectResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    try {
      $this->contactService->deleteContact($contact);

      return redirect()->route('contacts.index')
        ->with('success', 'Contact deleted successfully.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to delete contact: ' . $e->getMessage());
    }
  }

  /**
   * Search contacts (AJAX endpoint)
   */
  public function search(Request $request): JsonResponse
  {
    $query = $request->get('query', '');
    $filters = $request->only(['firm_id']);

    if (strlen($query) < 2) {
      return response()->json([]);
    }

    $contacts = $this->contactService->searchContacts($query, $filters);

    return response()->json($contacts->map(function ($contact) {
      return [
        'uuid' => $contact->uuid,
        'full_name' => $contact->full_name,
        'primary_email' => $contact->primary_email,
        'job_title' => $contact->job_title,
        'firm_name' => $contact->firm?->name,
        'avatar_url' => $contact->getFirstMediaUrl('avatar'),
      ];
    }));
  }

  /**
   * Duplicate a contact
   */
  public function duplicate(string $uuid): RedirectResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    try {
      $duplicatedContact = $this->contactService->duplicateContact($contact, [
        'first_name' => $contact->first_name . ' (Copy)',
      ]);

      return redirect()->route('contacts.edit', $duplicatedContact->uuid)
        ->with('success', 'Contact duplicated successfully.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to duplicate contact: ' . $e->getMessage());
    }
  }

  /**
   * Bulk delete contacts
   */
  public function bulkDelete(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'contact_ids' => 'required|array|min:1',
      'contact_ids.*' => 'exists:contacts,uuid',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Invalid contact IDs provided.',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      $deletedCount = $this->contactService->bulkDeleteContacts($request->contact_ids);

      return response()->json([
        'success' => true,
        'message' => "Successfully deleted {$deletedCount} contacts.",
        'deleted_count' => $deletedCount,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to delete contacts: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Export contacts
   */
  public function export(Request $request)
  {
    $filters = $request->only(['firm_id', 'tags']);
    $format = $request->get('format', 'csv');

    try {
      $contacts = $this->contactService->exportContacts($filters);

      if ($format === 'json') {
        return response()->json($contacts);
      }

      // Generate CSV
      $filename = 'contacts_' . date('Y-m-d_H-i-s') . '.csv';
      $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
      ];

      $callback = function () use ($contacts) {
        $file = fopen('php://output', 'w');

        // Add CSV headers
        if (!empty($contacts)) {
          fputcsv($file, array_keys($contacts[0]));
        }

        // Add data rows
        foreach ($contacts as $contact) {
          fputcsv($file, $contact);
        }

        fclose($file);
      };

      return response()->stream($callback, 200, $headers);
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to export contacts: ' . $e->getMessage());
    }
  }

  /**
   * Get contacts by firm (AJAX endpoint)
   */
  public function getByFirm(string $firmId): JsonResponse
  {
    try {
      $contacts = $this->contactService->getContactsByFirm($firmId);

      return response()->json($contacts->map(function ($contact) {
        return [
          'uuid' => $contact->uuid,
          'full_name' => $contact->full_name,
          'primary_email' => $contact->primary_email,
          'job_title' => $contact->job_title,
          'avatar_url' => $contact->getFirstMediaUrl('avatar'),
        ];
      }));
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to fetch contacts: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Add email to contact
   */
  public function addEmail(Request $request, string $uuid): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    $validator = Validator::make($request->all(), [
      'email' => 'required|email|max:255',
      'is_primary' => 'boolean',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      $email = $this->contactService->addEmail(
        $contact,
        $request->email,
        $request->boolean('is_primary', false)
      );

      return response()->json([
        'success' => true,
        'message' => 'Email added successfully',
        'email' => $email,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to add email: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Add phone to contact
   */
  public function addPhone(Request $request, string $uuid): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    $validator = Validator::make($request->all(), [
      'phone' => 'required|phone:AUTO,MW,ZA,ZW',
      'country_code' => 'nullable|string|max:5',
      'type' => 'nullable|string|in:mobile,home,work,fax',
      'is_primary' => 'boolean',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      $phone = $this->contactService->addPhone(
        $contact,
        $request->phone,
        $request->get('country_code', '+265'),
        $request->get('type', 'mobile'),
        $request->boolean('is_primary', false)
      );

      return response()->json([
        'success' => true,
        'message' => 'Phone added successfully',
        'phone' => $phone,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to add phone: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Remove email from contact
   */
  public function removeEmail(Request $request, string $uuid, string $emailId): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    try {
      $success = $this->contactService->removeEmail($contact, $emailId);

      if (!$success) {
        return response()->json([
          'success' => false,
          'message' => 'Email not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Email removed successfully',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to remove email: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Remove phone from contact
   */
  public function removePhone(Request $request, string $uuid, string $phoneId): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    try {
      $success = $this->contactService->removePhone($contact, $phoneId);

      if (!$success) {
        return response()->json([
          'success' => false,
          'message' => 'Phone not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Phone removed successfully',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to remove phone: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Set primary email for contact
   */
  public function setPrimaryEmail(Request $request, string $uuid, string $emailId): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    try {
      $success = $this->contactService->setPrimaryEmail($contact, $emailId);

      if (!$success) {
        return response()->json([
          'success' => false,
          'message' => 'Email not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Primary email updated successfully',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update primary email: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Set primary phone for contact
   */
  public function setPrimaryPhone(Request $request, string $uuid, string $phoneId): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    try {
      $success = $this->contactService->setPrimaryPhone($contact, $phoneId);

      if (!$success) {
        return response()->json([
          'success' => false,
          'message' => 'Phone not found',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'Primary phone updated successfully',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to update primary phone: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Merge two contacts
   */
  public function merge(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'primary_contact_id' => 'required|exists:contacts,uuid',
      'secondary_contact_id' => 'required|exists:contacts,uuid|different:primary_contact_id',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      $primaryContact = $this->contactService->getContactByUuid($request->primary_contact_id);
      $secondaryContact = $this->contactService->getContactByUuid($request->secondary_contact_id);

      if (!$primaryContact || !$secondaryContact) {
        return response()->json([
          'success' => false,
          'message' => 'One or both contacts not found',
        ], 404);
      }

      $mergedContact = $this->contactService->mergeContacts($primaryContact, $secondaryContact);

      return response()->json([
        'success' => true,
        'message' => 'Contacts merged successfully',
        'contact' => $mergedContact,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to merge contacts: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Restore a soft-deleted contact
   */
  public function restore(string $uuid): RedirectResponse
  {
    $contact = Contact::withTrashed()->where('uuid', $uuid)->first();

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    if (!$contact->trashed()) {
      return redirect()->back()
        ->with('error', 'Contact is not deleted');
    }

    try {
      $this->contactService->restoreContact($contact);

      return redirect()->route('contacts.show', $contact->uuid)
        ->with('success', 'Contact restored successfully.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to restore contact: ' . $e->getMessage());
    }
  }

  /**
   * Force delete a contact (permanent deletion)
   */
  public function forceDelete(string $uuid): RedirectResponse
  {
    $contact = Contact::withTrashed()->where('uuid', $uuid)->first();

    if (!$contact) {
      abort(404, 'Contact not found');
    }

    try {
      $this->contactService->forceDeleteContact($contact);

      return redirect()->route('contacts.index')
        ->with('success', 'Contact permanently deleted.');
    } catch (\Exception $e) {
      return redirect()->back()
        ->with('error', 'Failed to permanently delete contact: ' . $e->getMessage());
    }
  }

  /**
   * Get orphaned contacts (contacts without firms)
   */
  public function orphaned(): Response
  {
    $contacts = $this->contactService->getOrphanedContacts();

    return Inertia::render('contacts/Orphaned', [
      'contacts' => $contacts,
    ]);
  }

  /**
   * Get contacts with duplicate emails
   */
  public function duplicates(): Response
  {
    $contacts = $this->contactService->getDuplicateEmailContacts();

    return Inertia::render('contacts/Duplicates', [
      'contacts' => $contacts,
    ]);
  }

  /**
   * Import contacts from file
   */
  public function import(Request $request): JsonResponse
  {
    $validator = Validator::make($request->all(), [
      'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      $file = $request->file('file');
      $path = $file->getRealPath();
      $data = array_map('str_getcsv', file($path));

      // Remove header row
      $headers = array_shift($data);

      // Convert to associative array
      $contactsData = [];
      foreach ($data as $row) {
        if (count($row) === count($headers)) {
          $contactsData[] = array_combine($headers, $row);
        }
      }

      $results = $this->contactService->importContacts($contactsData);

      return response()->json([
        'success' => true,
        'message' => "Import completed. {$results['success']} contacts imported, {$results['failed']} failed.",
        'results' => $results,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to import contacts: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Show import form
   */
  public function showImport(): Response
  {
    return Inertia::render('contacts/Import');
  }

  /**
   * Get contact statistics for dashboard
   */
  public function stats(): JsonResponse
  {
    try {
      $stats = $this->contactService->getContactStats();

      return response()->json([
        'success' => true,
        'stats' => $stats,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to fetch statistics: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Get recent contacts
   */
  public function recent(Request $request): JsonResponse
  {
    $limit = $request->get('limit', 10);

    try {
      $contacts = $this->contactService->getRecentContacts($limit);

      return response()->json($contacts->map(function ($contact) {
        return [
          'uuid' => $contact->uuid,
          'full_name' => $contact->full_name,
          'primary_email' => $contact->primary_email,
          'job_title' => $contact->job_title,
          'firm_name' => $contact->firm?->name,
          'avatar_url' => $contact->getFirstMediaUrl('avatar'),
          'created_at' => $contact->created_at,
        ];
      }));
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to fetch recent contacts: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Upload avatar for contact
   */
  public function uploadAvatar(Request $request, string $uuid): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    $validator = Validator::make($request->all(), [
      'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
      return response()->json([
        'success' => false,
        'message' => 'Validation failed',
        'errors' => $validator->errors(),
      ], 422);
    }

    try {
      // Clear existing avatar
      $contact->clearMediaCollection('avatar');

      // Add new avatar
      $media = $contact->addMediaFromRequest('avatar')
        ->toMediaCollection('avatar');

      return response()->json([
        'success' => true,
        'message' => 'Avatar uploaded successfully',
        'avatar_url' => $media->getUrl(),
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload avatar: ' . $e->getMessage(),
      ], 500);
    }
  }

  /**
   * Remove avatar from contact
   */
  public function removeAvatar(string $uuid): JsonResponse
  {
    $contact = $this->contactService->getContactByUuid($uuid);

    if (!$contact) {
      return response()->json([
        'success' => false,
        'message' => 'Contact not found',
      ], 404);
    }

    try {
      $contact->clearMediaCollection('avatar');

      return response()->json([
        'success' => true,
        'message' => 'Avatar removed successfully',
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to remove avatar: ' . $e->getMessage(),
      ], 500);
    }
  }
}
