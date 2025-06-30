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
    } while (Email::where('email', $newEmail)->exists());

    return $newEmail;
  }

  /**
   * Export contacts to array
   */
  public function exportContacts(array $filters = []): array
  {
    $query = Contact::with(['firm', 'tags', 'emails', 'phones']);

    // Apply filters similar to getPaginatedContacts
    if (!empty($filters['firm_id'])) {
      $query->where('firm_id', $filters['firm_id']);
    }

    if (!empty($filters['tags'])) {
      $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
      $query->whereHas('tags', function ($q) use ($tags) {
        $q->whereIn('name', $tags);
      });
    }

    return $query->get()->map(function ($contact) {
      return [
        'uuid' => $contact->uuid,
        'first_name' => $contact->first_name,
        'last_name' => $contact->last_name,
        'middle_name' => $contact->middle_name,
        'nickname' => $contact->nickname,
        'full_name' => $contact->full_name,
        'primary_email' => $contact->primary_email,
        'primary_phone' => $contact->primary_phone_number,
        'job_title' => $contact->job_title,
        'title' => $contact->title,
        'bio' => $contact->bio,
        'firm' => $contact->firm?->name,
        'tags' => $contact->tags->pluck('name')->implode(', '),
        'avatar_url' => $contact->avatar_url,
        'created_at' => $contact->created_at?->format('Y-m-d H:i:s'),
        'updated_at' => $contact->updated_at?->format('Y-m-d H:i:s'),
      ];
    })->toArray();
  }

  /**
   * Add additional email to contact
   */
  public function addEmail(Contact $contact, string $email, bool $isPrimary = false): Email
  {
    if ($isPrimary) {
      // Remove primary flag from existing emails
      $contact->emails()->update(['is_primary_email' => false]);
    }

    return $contact->emails()->create([
      'email' => $email,
      'is_primary_email' => $isPrimary,
    ]);
  }

  /**
   * Add additional phone to contact
   */
  public function addPhone(Contact $contact, string $phone, string $countryCode = '+1', string $type = 'mobile', bool $isPrimary = false): Phone
  {
    if ($isPrimary) {
      // Remove primary flag from existing phones
      $contact->phones()->update(['is_primary_phone' => false]);
    }

    return $contact->phones()->create([
      'number' => $phone,
      'formatted' => $phone,
      'country_code' => $countryCode,
      'type' => $type,
      'is_primary_phone' => $isPrimary,
    ]);
  }

  /**
   * Remove email from contact
   */
  public function removeEmail(Contact $contact, string $emailId): bool
  {
    $email = $contact->emails()->where('uuid', $emailId)->first();

    if (!$email) {
      return false;
    }

    // If removing primary email, make another email primary if exists
    if ($email->is_primary_email) {
      $nextEmail = $contact->emails()->where('uuid', '!=', $emailId)->first();
      if ($nextEmail) {
        $nextEmail->update(['is_primary_email' => true]);
      }
    }

    return $email->delete();
  }

  /**
   * Remove phone from contact
   */
  public function removePhone(Contact $contact, string $phoneId): bool
  {
    $phone = $contact->phones()->where('uuid', $phoneId)->first();

    if (!$phone) {
      return false;
    }

    // If removing primary phone, make another phone primary if exists
    if ($phone->is_primary_phone) {
      $nextPhone = $contact->phones()->where('uuid', '!=', $phoneId)->first();
      if ($nextPhone) {
        $nextPhone->update(['is_primary_phone' => true]);
      }
    }

    return $phone->delete();
  }

  /**
   * Set primary email
   */
  public function setPrimaryEmail(Contact $contact, string $emailId): bool
  {
    $email = $contact->emails()->where('uuid', $emailId)->first();

    if (!$email) {
      return false;
    }

    // Remove primary flag from all emails
    $contact->emails()->update(['is_primary_email' => false]);

    // Set this email as primary
    $email->update(['is_primary_email' => true]);

    return true;
  }

  /**
   * Set primary phone
   */
  public function setPrimaryPhone(Contact $contact, string $phoneId): bool
  {
    $phone = $contact->phones()->where('uuid', $phoneId)->first();

    if (!$phone) {
      return false;
    }

    // Remove primary flag from all phones
    $contact->phones()->update(['is_primary_phone' => false]);

    // Set this phone as primary
    $phone->update(['is_primary_phone' => true]);

    return true;
  }

  /**
   * Get contacts without firms (orphaned contacts)
   */
  public function getOrphanedContacts(): Collection
  {
    return Contact::with(['tags', 'emails', 'phones'])
      ->whereNull('firm_id')
      ->orderBy('first_name')
      ->orderBy('last_name')
      ->get();
  }

  /**
   * Get contacts with duplicate emails
   */
  public function getDuplicateEmailContacts(): Collection
  {
    $duplicateEmails = Email::select('email')
      ->groupBy('email')
      ->havingRaw('COUNT(*) > 1')
      ->pluck('email');

    return Contact::with(['firm', 'tags', 'emails', 'phones'])
      ->whereHas('emails', function ($query) use ($duplicateEmails) {
        $query->whereIn('email', $duplicateEmails);
      })
      ->get();
  }

  /**
   * Merge two contacts
   */
  public function mergeContacts(Contact $primaryContact, Contact $secondaryContact): Contact
  {
    return DB::transaction(function () use ($primaryContact, $secondaryContact) {
      // Merge emails (avoid duplicates)
      $secondaryEmails = $secondaryContact->emails;
      foreach ($secondaryEmails as $email) {
        $existingEmail = $primaryContact->emails()->where('email', $email->email)->first();
        if (!$existingEmail) {
          $email->update(['contact_id' => $primaryContact->id]);
        }
      }

      // Merge phones (avoid duplicates)
      $secondaryPhones = $secondaryContact->phones;
      foreach ($secondaryPhones as $phone) {
        $existingPhone = $primaryContact->phones()->where('number', $phone->number)->first();
        if (!$existingPhone) {
          $phone->update(['contact_id' => $primaryContact->id]);
        }
      }

      // Merge tags
      $secondaryTags = $secondaryContact->tags->pluck('id')->toArray();
      $primaryTags = $primaryContact->tags->pluck('id')->toArray();
      $allTags = array_unique(array_merge($primaryTags, $secondaryTags));
      $primaryContact->tags()->sync($allTags);

      // Update primary contact with any missing information
      $updateData = [];
      if (empty($primaryContact->bio) && !empty($secondaryContact->bio)) {
        $updateData['bio'] = $secondaryContact->bio;
      }
      if (empty($primaryContact->job_title) && !empty($secondaryContact->job_title)) {
        $updateData['job_title'] = $secondaryContact->job_title;
      }
      if (empty($primaryContact->title) && !empty($secondaryContact->title)) {
        $updateData['title'] = $secondaryContact->title;
      }
      if (empty($primaryContact->middle_name) && !empty($secondaryContact->middle_name)) {
        $updateData['middle_name'] = $secondaryContact->middle_name;
      }
      if (empty($primaryContact->nickname) && !empty($secondaryContact->nickname)) {
        $updateData['nickname'] = $secondaryContact->nickname;
      }

      if (!empty($updateData)) {
        $primaryContact->update($updateData);
      }

      // Transfer media if primary contact doesn't have avatar
      if (!$primaryContact->hasMedia('avatar') && $secondaryContact->hasMedia('avatar')) {
        $media = $secondaryContact->getFirstMedia('avatar');
        if ($media) {
          $media->move($primaryContact, 'avatar');
        }
      }

      // Delete secondary contact
      $this->forceDeleteContact($secondaryContact);

      return $primaryContact->load(['firm', 'tags', 'emails', 'phones']);
    });
  }

  /**
   * Get contact activity summary
   */
  public function getContactActivity(Contact $contact): array
  {
    return [
      'total_emails' => $contact->emails()->count(),
      'total_phones' => $contact->phones()->count(),
      'total_tags' => $contact->tags()->count(),
      'has_avatar' => $contact->hasMedia('avatar'),
      'has_documents' => $contact->hasMedia('documents'),
      'created_days_ago' => $contact->created_at?->diffInDays(now()),
      'last_updated_days_ago' => $contact->updated_at?->diffInDays(now()),
    ];
  }

  /**
   * Import contacts from array data
   */
  public function importContacts(array $contactsData): array
  {
    $results = [
      'success' => 0,
      'failed' => 0,
      'errors' => []
    ];

    foreach ($contactsData as $index => $contactData) {
      try {
        $this->createContact($contactData);
        $results['success']++;
      } catch (\Exception $e) {
        $results['failed']++;
        $results['errors'][] = [
          'row' => $index + 1,
          'error' => $e->getMessage(),
          'data' => $contactData
        ];
      }
    }

    return $results;
  }
}
