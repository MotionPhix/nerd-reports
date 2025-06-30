<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Tags\HasTags;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Traits\HasUuid;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Contact extends Model implements HasMedia
{
  use HasFactory, SoftDeletes, HasTags, HasUuid, InteractsWithMedia;

  protected $table = 'contacts';

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = [
    'first_name',
    'last_name',
    'bio',
    'job_title',
    'title',
    'middle_name',
    'firm_id',
    'nickname'
  ];

  protected $appends = [
    'full_name',
    'primary_email',
    'primary_phone_number',
    'avatar_url',
    'avatar_thumbnail_url',
  ];

  protected function casts(): array
  {
    return [
      'created_at' => 'datetime',
      'updated_at' => 'datetime',
      'deleted_at' => 'datetime',
      'last_interaction' => 'datetime',
      'bio' => PurifyHtmlOnGet::class,
    ];
  }

  /**
   * Register media collections
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('avatar')
      ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
      ->singleFile()
      ->useDisk('public');

    $this->addMediaCollection('documents')
      ->acceptsMimeTypes([
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/plain',
        'text/csv'
      ])
      ->useDisk('private');
  }

  /**
   * Register media conversions
   */
  public function registerMediaConversions(Media $media = null): void
  {
    $this->addMediaConversion('thumb')
      ->width(150)
      ->height(150)
      ->sharpen(10)
      ->optimize()
      ->nonQueued()
      ->performOnCollections('avatar');

    $this->addMediaConversion('medium')
      ->width(300)
      ->height(300)
      ->sharpen(10)
      ->optimize()
      ->nonQueued()
      ->performOnCollections('avatar');
  }

  /**
   * Phone numbers relationship (one-to-many polymorphic)
   */
  public function phones(): MorphMany
  {
    return $this->morphMany(Phone::class, 'phoneable');
  }

  /**
   * Email addresses relationship (one-to-many polymorphic)
   */
  public function emails(): MorphMany
  {
    return $this->morphMany(Email::class, 'emailable');
  }

  /**
   * Interactions relationship
   */
  public function interactions(): HasMany
  {
    return $this->hasMany(Interaction::class, 'contact_id', 'uuid');
  }

  /**
   * Firm relationship
   */
  public function firm(): BelongsTo
  {
    return $this->belongsTo(Firm::class, 'firm_id', 'uuid');
  }

  /**
   * Projects relationship
   */
  public function projects(): HasMany
  {
    return $this->hasMany(Project::class, 'contact_id', 'uuid');
  }

  /**
   * Active projects relationship
   */
  public function activeProjects(): HasMany
  {
    return $this->hasMany(Project::class, 'contact_id', 'uuid')
      ->where('status', 'active');
  }

  /**
   * Recent interactions relationship
   */
  public function recentInteractions(): HasMany
  {
    return $this->hasMany(Interaction::class, 'contact_id', 'uuid')
      ->latest('interaction_date')
      ->limit(10);
  }

  /**
   * Get full name accessor
   */
  protected function fullName(): Attribute
  {
    return Attribute::make(
      get: fn() => trim("{$this->first_name} {$this->last_name}")
    );
  }

  /**
   * Get primary email accessor
   */
  public function primaryEmail(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->emails->firstWhere('is_primary_email', true)?->email ?? null
    );
  }

  /**
   * Get primary phone number accessor
   */
  public function primaryPhoneNumber(): Attribute
  {
    return Attribute::make(
      get: fn() => $this->phones->firstWhere('is_primary_phone', true)?->formatted ?? null
    );
  }

  /**
   * Get avatar URL accessor
   */
  public function getAvatarUrlAttribute(): ?string
  {
    return $this->getFirstMediaUrl('avatar') ?: $this->generateFallbackAvatar();
  }

  /**
   * Get avatar thumbnail URL accessor
   */
  public function getAvatarThumbnailUrlAttribute(): ?string
  {
    return $this->getFirstMediaUrl('avatar', 'thumb') ?: $this->generateFallbackAvatar();
  }

  /**
   * Generate fallback avatar URL
   */
  private function generateFallbackAvatar(): string
  {
    $initials = $this->getInitials();
    return "https://ui-avatars.com/api/?name={$initials}&size=150&background=2563eb&color=ffffff&bold=true";
  }

  /**
   * Get contact initials for fallback avatar
   */
  public function getInitials(): string
  {
    $firstInitial = $this->first_name ? strtoupper(substr($this->first_name, 0, 1)) : '';
    $lastInitial = $this->last_name ? strtoupper(substr($this->last_name, 0, 1)) : '';

    return $firstInitial . $lastInitial;
  }

  /**
   * Get display name (with nickname if available)
   */
  public function getDisplayNameAttribute(): string
  {
    if ($this->nickname) {
      return "{$this->full_name} ({$this->nickname})";
    }

    return $this->full_name;
  }

  /**
   * Get contact's job info
   */
  public function getJobInfoAttribute(): ?string
  {
    $parts = array_filter([
      $this->job_title,
      $this->firm?->name
    ]);

    return !empty($parts) ? implode(' at ', $parts) : null;
  }

  /**
   * Scope for search
   */
  public function scopeSearch(Builder $query, string $search): Builder
  {
    return $query->where(function (Builder $q) use ($search) {
      $q->where('first_name', 'like', "%{$search}%")
        ->orWhere('last_name', 'like', "%{$search}%")
        ->orWhere('middle_name', 'like', "%{$search}%")
        ->orWhere('nickname', 'like', "%{$search}%")
        ->orWhere('job_title', 'like', "%{$search}%")
        ->orWhere('title', 'like', "%{$search}%")
        ->orWhereHas('emails', function (Builder $emailQuery) use ($search) {
          $emailQuery->where('email', 'like', "%{$search}%");
        })
        ->orWhereHas('phones', function (Builder $phoneQuery) use ($search) {
          $phoneQuery->where('number', 'like', "%{$search}%")
            ->orWhere('formatted', 'like', "%{$search}%");
        })
        ->orWhereHas('firm', function (Builder $firmQuery) use ($search) {
          $firmQuery->where('name', 'like', "%{$search}%");
        });
    });
  }

  /**
   * Scope for contacts by firm
   */
  public function scopeByFirm(Builder $query, string $firmId): Builder
  {
    return $query->where('firm_id', $firmId);
  }

  /**
   * Scope for orphaned contacts (without firms)
   */
  public function scopeOrphaned(Builder $query): Builder
  {
    return $query->whereNull('firm_id');
  }

  /**
   * Scope for contacts with firms
   */
  public function scopeWithFirm(Builder $query): Builder
  {
    return $query->whereNotNull('firm_id');
  }

  /**
   * Scope for contacts with emails
   */
  public function scopeWithEmails(Builder $query): Builder
  {
    return $query->whereHas('emails');
  }

  /**
   * Scope for contacts with phones
   */
  public function scopeWithPhones(Builder $query): Builder
  {
    return $query->whereHas('phones');
  }

  /**
   * Scope for recent contacts
   */
  public function scopeRecent(Builder $query, int $days = 30): Builder
  {
    return $query->where('created_at', '>=', now()->subDays($days));
  }

  /**
   * Scope with counts for optimization
   */
  public function scopeWithCounts(Builder $query): Builder
  {
    return $query->withCount(['emails', 'phones', 'tags', 'interactions', 'projects'])
      ->selectRaw('contacts.*,
                        (SELECT COUNT(*) FROM projects
                         WHERE projects.contact_id = contacts.uuid AND projects.status = "active") as active_projects_count')
      ->selectRaw('(SELECT MAX(interaction_date) FROM interactions
                         WHERE interactions.contact_id = contacts.uuid) as last_interaction_date');
  }

  /**
   * Boot method for model events
   */
  protected static function boot(): void
  {
    parent::boot();

    static::forceDeleting(function ($contact) {
      $contact->load('phones', 'emails', 'tags');

      // Delete related phones and emails
      $contact->phones()->delete();
      $contact->emails()->delete();

      // Detach tags
      $contact->tags()->detach();

      // Clear media collections
      $contact->clearMediaCollection('avatar');
      $contact->clearMediaCollection('documents');
    });
  }

  /**
   * Add primary email to contact
   */
  public function addPrimaryEmail(string $email): Email
  {
    // Remove primary flag from existing emails
    $this->emails()->update(['is_primary_email' => false]);

    return $this->emails()->create([
      'email' => $email,
      'is_primary_email' => true,
    ]);
  }

  /**
   * Add primary phone to contact
   */
  public function addPrimaryPhone(string $phone, string $countryCode = '+265', string $type = 'mobile'): Phone
  {
    // Remove primary flag from existing phones
    $this->phones()->update(['is_primary_phone' => false]);

    return $this->phones()->create([
      'number' => $phone,
      'formatted' => $phone,
      'country_code' => $countryCode,
      'type' => $type,
      'is_primary_phone' => true,
    ]);
  }

  /**
   * Get contact's primary phone
   */
  public function getPrimaryPhone(): ?Phone
  {
    return $this->phones()->where('is_primary_phone', true)->first();
  }

  /**
   * Get contact's primary email
   */
  public function getPrimaryEmailModel(): ?Email
  {
    return $this->emails()->where('is_primary_email', true)->first();
  }

  /**
   * Check if contact has active projects
   */
  public function hasActiveProjects(): bool
  {
    return $this->activeProjects()->exists();
  }

  /**
   * Get last interaction date
   */
  public function getLastInteractionDate(): ?string
  {
    return $this->interactions()
      ->latest('interaction_date')
      ->first()?->interaction_date;
  }

  /**
   * Get contact activity score based on recent interactions
   */
  public function getActivityScore(): int
  {
    $recentInteractions = $this->interactions()
      ->where('interaction_date', '>=', now()->subDays(30))
      ->count();

    $activeProjects = $this->activeProjects()->count();

    // Simple scoring algorithm
    $score = ($recentInteractions * 2) + ($activeProjects * 5);

    return min($score, 100); // Cap at 100
  }

  /**
   * Upload avatar
   */
  public function uploadAvatar($file): Media
  {
    return $this->addMedia($file)
      ->toMediaCollection('avatar');
  }

  /**
   * Upload document
   */
  public function uploadDocument($file, string $name = null): Media
  {
    $mediaAdder = $this->addMedia($file)
      ->toMediaCollection('documents');

    if ($name) {
      $mediaAdder->usingName($name);
    }

    return $mediaAdder;
  }

  /**
   * Get all documents
   */
  public function getDocuments()
  {
    return $this->getMedia('documents');
  }

  /**
   * Convert contact to array for API responses
   */
  public function toApiArray(): array
  {
    return [
      'uuid' => $this->uuid,
      'first_name' => $this->first_name,
      'last_name' => $this->last_name,
      'middle_name' => $this->middle_name,
      'nickname' => $this->nickname,
      'full_name' => $this->full_name,
      'display_name' => $this->display_name,
      'job_title' => $this->job_title,
      'title' => $this->title,
      'job_info' => $this->job_info,
      'bio' => $this->bio,
      'primary_email' => $this->primary_email,
      'primary_phone' => $this->primary_phone_number,
      'avatar_url' => $this->avatar_url,
      'avatar_thumbnail_url' => $this->avatar_thumbnail_url,
      'initials' => $this->getInitials(),
      'firm' => $this->firm?->toApiArray(),
      'emails' => $this->emails->map(function ($email) {
        return [
          'uuid' => $email->uuid,
          'email' => $email->email,
          'is_primary' => $email->is_primary_email,
          'verified_at' => $email->verified_at?->toISOString(),
        ];
      }),
      'phones' => $this->phones->map(function ($phone) {
        return [
          'uuid' => $phone->uuid,
          'number' => $phone->number,
          'formatted' => $phone->formatted,
          'country_code' => $phone->country_code,
          'type' => $phone->type,
          'is_primary' => $phone->is_primary_phone,
        ];
      }),
      'tags' => $this->tags->pluck('name'),
      'emails_count' => $this->emails_count ?? $this->emails->count(),
      'phones_count' => $this->phones_count ?? $this->phones->count(),
      'tags_count' => $this->tags_count ?? $this->tags->count(),
      'interactions_count' => $this->interactions_count ?? 0,
      'projects_count' => $this->projects_count ?? 0,
      'active_projects_count' => $this->active_projects_count ?? 0,
      'has_active_projects' => $this->hasActiveProjects(),
      'activity_score' => $this->getActivityScore(),
      'last_interaction_date' => $this->getLastInteractionDate(),
      'has_avatar' => $this->hasMedia('avatar'),
      'documents_count' => $this->getMedia('documents')->count(),
      'created_at' => $this->created_at?->toISOString(),
      'updated_at' => $this->updated_at?->toISOString(),
    ];
  }

  /**
   * Get contact summary for quick display
   */
  public function getSummary(): array
  {
    return [
      'uuid' => $this->uuid,
      'full_name' => $this->full_name,
      'job_title' => $this->job_title,
      'firm_name' => $this->firm?->name,
      'primary_email' => $this->primary_email,
      'primary_phone' => $this->primary_phone_number,
      'avatar_url' => $this->avatar_url,
    ];
  }

  /**
   * Check if contact is complete (has minimum required info)
   */
  public function isComplete(): bool
  {
    return !empty($this->first_name) &&
      !empty($this->last_name) &&
      ($this->emails()->exists() || $this->phones()->exists());
  }

  /**
   * Get completion percentage
   */
  public function getCompletionPercentage(): int
  {
    $fields = [
      'first_name' => !empty($this->first_name),
      'last_name' => !empty($this->last_name),
      'job_title' => !empty($this->job_title),
      'bio' => !empty($this->bio),
      'firm_id' => !empty($this->firm_id),
      'has_email' => $this->emails()->exists(),
      'has_phone' => $this->phones()->exists(),
      'has_avatar' => $this->hasMedia('avatar'),
    ];

    $completedFields = array_filter($fields);
    $totalFields = count($fields);
    $completedCount = count($completedFields);

    return round(($completedCount / $totalFields) * 100);
  }

  /**
   * Mark contact as recently viewed
   */
  public function markAsViewed(): void
  {
    $this->update(['last_viewed_at' => now()]);
  }

  /**
   * Get contact's social media links if firm has them
   */
  public function getSocialMediaLinks(): array
  {
    return $this->firm?->getSocialMediaLinks() ?? [];
  }

  /**
   * Check if contact can be deleted
   */
  public function canBeDeleted(): bool
  {
    // Don't allow deletion if contact has active projects
    return !$this->hasActiveProjects();
  }

  /**
   * Get contact's recent activity
   */
  public function getRecentActivity(int $limit = 5): array
  {
    $activities = [];

    // Recent interactions
    $recentInteractions = $this->interactions()
      ->latest('interaction_date')
      ->limit($limit)
      ->get();

    foreach ($recentInteractions as $interaction) {
      $activities[] = [
        'type' => 'interaction',
        'title' => $interaction->type,
        'description' => $interaction->notes,
        'date' => $interaction->interaction_date,
        'icon' => 'message-circle',
      ];
    }

    // Recent projects
    $recentProjects = $this->projects()
      ->latest('created_at')
      ->limit($limit)
      ->get();

    foreach ($recentProjects as $project) {
      $activities[] = [
        'type' => 'project',
        'title' => $project->name,
        'description' => "Project {$project->status}",
        'date' => $project->created_at,
        'icon' => 'briefcase',
      ];
    }

    // Sort by date and limit
    usort($activities, function ($a, $b) {
      return $b['date'] <=> $a['date'];
    });

    return array_slice($activities, 0, $limit);
  }

  /**
   * Search contacts with advanced filters
   */
  public static function advancedSearch(array $filters): Builder
  {
    $query = static::query()->with(['firm', 'emails', 'phones', 'tags']);

    if (!empty($filters['search'])) {
      $query->search($filters['search']);
    }

    if (!empty($filters['firm_id'])) {
      $query->byFirm($filters['firm_id']);
    }

    if (!empty($filters['has_email'])) {
      $query->withEmails();
    }

    if (!empty($filters['has_phone'])) {
      $query->withPhones();
    }

    if (!empty($filters['orphaned'])) {
      $query->orphaned();
    }

    if (!empty($filters['tags'])) {
      $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
      $query->withAnyTags($tags, 'contact');
    }

    if (!empty($filters['created_after'])) {
      $query->where('created_at', '>=', $filters['created_after']);
    }

    if (!empty($filters['created_before'])) {
      $query->where('created_at', '<=', $filters['created_before']);
    }

    return $query;
  }

  /**
   * Get duplicate contacts based on email
   */
  public static function findDuplicatesByEmail(): Builder
  {
    return static::query()
      ->whereHas('emails', function (Builder $query) {
        $query->whereIn('email', function ($subQuery) {
          $subQuery->select('email')
            ->from('emails')
            ->where('emailable_type', static::class)
            ->groupBy('email')
            ->havingRaw('COUNT(*) > 1');
        });
      })
      ->with(['emails', 'firm']);
  }

  /**
   * Get contacts that need attention (incomplete profiles, no recent activity)
   */
  public static function needsAttention(): Builder
  {
    return static::query()
      ->where(function (Builder $query) {
        // Incomplete profiles
        $query->whereNull('job_title')
          ->orWhereNull('firm_id')
          ->orWhereDoesntHave('emails')
          ->orWhereDoesntHave('phones');
      })
      ->orWhere(function (Builder $query) {
        // No recent interactions
        $query->whereDoesntHave('interactions', function (Builder $subQuery) {
          $subQuery->where('interaction_date', '>=', now()->subDays(90));
        });
      })
      ->with(['firm', 'emails', 'phones']);
  }
}
