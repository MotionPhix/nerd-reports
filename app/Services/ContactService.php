<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\Firm;
use App\Models\Email;
use App\Models\Phone;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ContactService
{
  /**
   * Get paginated contacts with optional filtering and searching
   */
  public function getPaginatedContacts(array $filters = [], int $perPage = 15): LengthAwarePaginator
  {
    $query = Contact::with(['firm', 'tags', 'emails', 'phones'])
      ->select('contacts.*');

    // Apply search filter
    if (!empty($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('first_name', 'like', "%{$search}%")
          ->orWhere('last_name', 'like', "%{$search}%")
          ->orWhere('middle_name', 'like', "%{$search}%")
          ->orWhere('nickname', 'like', "%{$search}%")
          ->orWhere('job_title', 'like', "%{$search}%")
          ->orWhere('title', 'like', "%{$search}%")
          ->orWhereHas('emails', function ($emailQuery) use ($search) {
            $emailQuery->where('email', 'like', "%{$search}%");
          })
          ->orWhereHas('phones', function ($phoneQuery) use ($search) {
            $phoneQuery->where('number', 'like', "%{$search}%")
              ->orWhere('formatted', 'like', "%{$search}%");
          })
          ->orWhereHas('firm', function ($firmQuery) use ($search) {
            $firmQuery->where('name', 'like', "%{$search}%");
          });
      });
    }

    // Apply firm filter
    if (!empty($filters['firm_id'])) {
      $query->where('firm_id', $filters['firm_id']);
    }

    // Apply tag filter
    if (!empty($filters['tags'])) {
      $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
      $query->whereHas('tags', function ($q) use ($tags) {
        $q->whereIn('name', $tags);
      });
    }

    // Apply sorting
    $sortBy = $filters['sort_by'] ?? 'created_at';
    $sortOrder = $filters['sort_order'] ?? 'desc';

    $allowedSortFields = [
      'first_name', 'last_name', 'job_title', 'title',
      'created_at', 'updated_at'
    ];

    if (in_array($sortBy, $allowedSortFields)) {
      $query->orderBy($sortBy, $sortOrder);
    }

    return $query->paginate($perPage);
  }

  /**
   * Get all contacts for a specific firm
   */
  public function getContactsByFirm(string $firmId): Collection
  {
    return Contact::where('firm_id', $firmId)
      ->with(['tags', 'emails', 'phones'])
      ->orderBy('first_name')
      ->orderBy('last_name')
      ->get();
  }

  /**
   * Create a new contact
   */
  public function createContact(array $data): Contact
  {
    return DB::transaction(function () use ($data) {
      // Extract avatar and contact data
      $avatar = $data['avatar'] ?? null;
      $emailData = $data['email'] ?? null;
      $phoneData = $data['phone'] ?? null;
      $tags = $data['tags'] ?? [];

      // Remove non-fillable fields from contact data
      $contactData = collect($data)->only([
        'first_name', 'last_name', 'bio', 'job_title',
        'title', 'middle_name', 'firm_id', 'nickname'
      ])->toArray();

      // Create the contact
      $contact = Contact::create($contactData);

      // Handle avatar upload if provided
      if ($avatar && $avatar instanceof UploadedFile) {
        $this->handleAvatarUpload($contact, $avatar);
      }

      // Handle primary email
      if ($emailData) {
        $this->addPrimaryEmail($contact, $emailData);
      }

      // Handle primary phone
      if ($phoneData) {
        $this->addPrimaryPhone($contact, $phoneData);
      }

      // Handle tags if provided
      if (!empty($tags) && is_array($tags)) {
        $this->syncContactTags($contact, $tags);
      }

      return $contact->load(['firm', 'tags', 'emails', 'phones']);
    });
  }

  /**
   * Update an existing contact
   */
  public function updateContact(Contact $contact, array $data): Contact
  {
    return DB::transaction(function () use ($contact, $data) {
      // Extract special fields
      $avatar = $data['avatar'] ?? null;
      $emailData = $data['email'] ?? null;
      $phoneData = $data['phone'] ?? null;
      $tags = $data['tags'] ?? null;

      // Update contact basic data
      $contactData = collect($data)->only([
        'first_name', 'last_name', 'bio', 'job_title',
        'title', 'middle_name', 'firm_id', 'nickname'
      ])->toArray();

      $contact->update($contactData);

      // Handle avatar upload if provided
      if ($avatar && $avatar instanceof UploadedFile) {
        $this->handleAvatarUpload($contact, $avatar);
      }

      // Handle primary email update
      if ($emailData) {
        $this->updatePrimaryEmail($contact, $emailData);
      }

      // Handle primary phone update
      if ($phoneData) {
        $this->updatePrimaryPhone($contact, $phoneData);
      }

      // Handle tags if provided
      if ($tags !== null && is_array($tags)) {
        $this->syncContactTags($contact, $tags);
      }

      return $contact->load(['firm', 'tags', 'emails', 'phones']);
    });
  }

  /**
   * Delete a contact (soft delete)
   */
  public function deleteContact(Contact $contact): bool
  {
    return DB::transaction(function () use ($contact) {
      // Clear media collections
      $contact->clearMediaCollection('avatar');
      $contact->clearMediaCollection('documents');

      // Detach all tags
      $contact->tags()->detach();

      // Soft delete the contact
      return $contact->delete();
    });
  }

  /**
   * Force delete a contact (permanent deletion)
   */
  public function forceDeleteContact(Contact $contact): bool
  {
    return DB::transaction(function () use ($contact) {
      // Clear media collections
      $contact->clearMediaCollection('avatar');
      $contact->clearMediaCollection('documents');

      // Delete related emails and phones
      $contact->emails()->delete();
      $contact->phones()->delete();

      // Detach all tags
      $contact->tags()->detach();

      // Force delete the contact
      return $contact->forceDelete();
    });
  }

  /**
   * Restore a soft-deleted contact
   */
  public function restoreContact(Contact $contact): bool
  {
    return $contact->restore();
  }

  /**
   * Get contact by UUID
   */
  public function getContactByUuid(string $uuid): ?Contact
  {
    return Contact::with(['firm', 'tags', 'emails', 'phones'])
      ->where('uuid', $uuid)
      ->first();
  }

  /**
   * Search contacts with advanced filters
   */
  public function searchContacts(string $query, array $filters = []): Collection
  {
    $searchQuery = Contact::with(['firm', 'tags', 'emails', 'phones'])
      ->where(function ($q) use ($query) {
        $q->where('first_name', 'like', "%{$query}%")
          ->orWhere('last_name', 'like', "%{$query}%")
          ->orWhere('middle_name', 'like', "%{$query}%")
          ->orWhere('nickname', 'like', "%{$query}%")
          ->orWhere('job_title', 'like', "%{$query}%")
          ->orWhere('title', 'like', "%{$query}%");
      });

    // Apply additional filters
    if (!empty($filters['firm_id'])) {
      $searchQuery->where('firm_id', $filters['firm_id']);
    }

    return $searchQuery->limit(50)->get();
  }

  /**
   * Get contact statistics
   */
  public function getContactStats(): array
  {
    return [
      'total' => Contact::count(),
      'recent' => Contact::where('created_at', '>=', now()->subDays(30))->count(),
      'with_firms' => Contact::whereNotNull('firm_id')->count(),
      'without_firms' => Contact::whereNull('firm_id')->count(),
      'with_emails' => Contact::whereHas('emails')->count(),
      'with_phones' => Contact::whereHas('phones')->count(),
    ];
  }

  /**
   * Get recent contacts
   */
  public function getRecentContacts(int $limit = 10): Collection
  {
    return Contact::with(['firm', 'tags', 'emails', 'phones'])
      ->orderBy('created_at', 'desc')
      ->limit($limit)
      ->get();
  }

  /**
   * Duplicate a contact
   */
  public function duplicateContact(Contact $contact, array $overrides = []): Contact
  {
    $data = $contact->only([
      'first_name', 'last_name', 'bio', 'job_title',
      'title', 'middle_name', 'firm_id', 'nickname'
    ]);

    // Apply overrides
    $data = array_merge($data, $overrides);

    // Get primary email and phone for duplication
    $primaryEmail = $contact->primary_email;
    $primaryPhone = $contact->phones()->where('is_primary_phone', true)->first();

    if ($primaryEmail && !isset($overrides['email'])) {
      $data['email'] = $this->generateUniqueEmail($primaryEmail);
    }

    if ($primaryPhone && !isset($overrides['phone'])) {
      $data['phone'] = $primaryPhone->number;
    }

    return $this->createContact($data);
  }

  /**
   * Bulk delete contacts
   */
  public function bulkDeleteContacts(array $contactIds): int
  {
    return Contact::whereIn('uuid', $contactIds)->delete();
  }

  /**
   * Handle avatar upload using Spatie Media Library
   */
  private function handleAvatarUpload(Contact $contact, UploadedFile $file): Media
  {
    // Clear existing avatar
    $contact->clearMediaCollection('avatar');

    return $contact->addMediaFromRequest('avatar')
      ->toMediaCollection('avatar');
  }

  /**
   * Add primary email to contact
   */
  private function addPrimaryEmail(Contact $contact, string $email): Email
  {
    // Remove primary flag from existing emails
    $contact->emails()->update(['is_primary_email' => false]);

    return $contact->emails()->create([
      'email' => $email,
      'is_primary_email' => true,
    ]);
  }

  /**
   * Update primary email for contact
   */
  private function updatePrimaryEmail(Contact $contact, string $email): Email
  {
    $primaryEmail = $contact->emails()->where('is_primary_email', true)->first();

    if ($primaryEmail) {
      $primaryEmail->update(['email' => $email]);
      return $primaryEmail;
    }

    return $this->addPrimaryEmail($contact, $email);
  }

  /**
   * Add primary phone to contact
   */
  private function addPrimaryPhone(Contact $contact, string $phone, string $countryCode = '+1', string $type = 'mobile'): Phone
  {
    // Remove primary flag from existing phones
    $contact->phones()->update(['is_primary_phone' => false]);

    return $contact->phones()->create([
      'number' => $phone,
      'formatted' => $phone,
      'country_code' => $countryCode,
      'type' => $type,
      'is_primary_phone' => true,
    ]);
  }

  /**
   * Update primary phone for contact
   */
  private function updatePrimaryPhone(Contact $contact, string $phone, string $countryCode = '+1', string $type = 'mobile'): Phone
  {
    $primaryPhone = $contact->phones()->where('is_primary_phone', true)->first();

    if ($primaryPhone) {
      $primaryPhone->update([
        'number' => $phone,
        'formatted' => $phone,
        'country_code' => $countryCode,
        'type' => $type,
      ]);
      return $primaryPhone;
    }

    return $this->addPrimaryPhone($contact, $phone, $countryCode, $type);
  }

  /**
   * Sync contact tags
   */
  private function syncContactTags(Contact $contact, array $tagNames): void
  {
    $tagIds = [];

    foreach ($tagNames as $tagName) {
      if (empty($tagName)) continue;

      $tag = \Spatie\Tags\Tag::findOrCreate($tagName, 'contact');
      $tagIds[] = $tag->id;
    }

    $contact->tags()->sync($tagIds);
  }

  /**
   * Generate unique email for duplicated contact
   */
  private function generateUniqueEmail(string $originalEmail): string
  {
    $parts = explode('@', $originalEmail);
    $localPart = $parts[0];
    $domain = $parts[1];

    $counter = 1;
    do {
      $newEmail = $localPart . '_' . $counter . '@' . $domain;
      $counter++;
    } while
