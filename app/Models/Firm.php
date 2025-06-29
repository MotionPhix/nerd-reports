<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

class Firm extends Model implements HasMedia
{
  use HasFactory, HasTags, HasUuid, InteractsWithMedia;

  protected $table = 'firms';

  protected $primaryKey = 'uuid';

  public $incrementing = false;

  protected $keyType = 'string';

  protected $fillable = [
    'name',
    'slogan',
    'url',
    'description',
    'industry',
    'size',
    'status',
    'priority',
    'source',
    'assigned_to',
    'metadata',
    'notes',
    'linkedin_url',
    'twitter_url',
    'facebook_url',
    'total_revenue',
  ];

  protected $casts = [
    'metadata' => 'array',
    'total_revenue' => 'decimal:2',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
  ];

  protected $appends = [
    'primary_email',
    'primary_phone',
    'full_address',
    'status_label',
    'priority_label',
    'logo_url',
    'logo_thumbnail_url',
  ];

  /**
   * Register media collections
   */
  public function registerMediaCollections(): void
  {
    $this->addMediaCollection('logo')
      ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'])
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

    $this->addMediaCollection('images')
      ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
      ->useDisk('public');
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
      ->performOnCollections('logo', 'images');

    $this->addMediaConversion('medium')
      ->width(300)
      ->height(300)
      ->sharpen(10)
      ->optimize()
      ->nonQueued()
      ->performOnCollections('logo', 'images');

    $this->addMediaConversion('large')
      ->width(600)
      ->height(600)
      ->sharpen(10)
      ->optimize()
      ->performOnCollections('logo', 'images');
  }

  /**
   * Get logo URL accessor
   */
  public function getLogoUrlAttribute(): ?string
  {
    return $this->getFirstMediaUrl('logo');
  }

  /**
   * Get logo thumbnail URL accessor
   */
  public function getLogoThumbnailUrlAttribute(): ?string
  {
    return $this->getFirstMediaUrl('logo', 'thumb');
  }

  /**
   * Address relationship (one-to-one polymorphic)
   */
  public function address(): MorphOne
  {
    return $this->morphOne(Address::class, 'addressable');
  }

  /**
   * Email addresses relationship (one-to-many polymorphic)
   */
  public function emails(): MorphMany
  {
    return $this->morphMany(Email::class, 'emailable');
  }

  /**
   * Phone numbers relationship (one-to-many polymorphic)
   */
  public function phones(): MorphMany
  {
    return $this->morphMany(Phone::class, 'phoneable');
  }

  /**
   * Contacts relationship
   */
  public function contacts(): HasMany
  {
    return $this->hasMany(Contact::class, 'firm_id', 'uuid');
  }

  /**
   * Projects relationship through contacts
   */
  public function projects(): HasManyThrough
  {
    return $this->hasManyThrough(Project::class, Contact::class, 'firm_id', 'contact_id', 'uuid', 'uuid');
  }

  /**
   * Active projects relationship
   */
  public function activeProjects(): HasManyThrough
  {
    return $this->hasManyThrough(Project::class, Contact::class, 'firm_id', 'contact_id', 'uuid', 'uuid')
      ->where('projects.status', 'active');
  }

  /**
   * Interactions relationship through contacts
   */
  public function interactions(): HasManyThrough
  {
    return $this->hasManyThrough(Interaction::class, Contact::class, 'firm_id', 'contact_id', 'uuid', 'uuid');
  }

  /**
   * Recent interactions relationship
   */
  public function recentInteractions(): HasManyThrough
  {
    return $this->hasManyThrough(Interaction::class, Contact::class, 'firm_id', 'contact_id', 'uuid', 'uuid')
      ->latest('interaction_date')
      ->limit(10);
  }

  /**
   * Get primary email accessor
   */
  public function getPrimaryEmailAttribute(): ?string
  {
    return $this->emails()->where('is_primary_email', true)->first()?->email;
  }

  /**
   * Get primary phone accessor
   */
  public function getPrimaryPhoneAttribute(): ?string
  {
    return $this->phones()->where('is_primary_phone', true)->first()?->formatted;
  }

  /**
   * Get full address accessor
   */
  public function getFullAddressAttribute(): ?string
  {
    if (!$this->address) {
      return null;
    }

    $parts = array_filter([
      $this->address->street,
      $this->address->city,
      $this->address->state,
      $this->address->country,
    ]);

    return implode(', ', $parts);
  }

  /**
   * Get status label accessor
   */
  public function getStatusLabelAttribute(): string
  {
    return match($this->status) {
      'active' => 'Active',
      'inactive' => 'Inactive',
      'prospect' => 'Prospect',
      default => 'Unknown'
    };
  }

  /**
   * Get priority label accessor
   */
  public function getPriorityLabelAttribute(): string
  {
    return match($this->priority) {
      'low' => 'Low Priority',
      'medium' => 'Medium Priority',
      'high' => 'High Priority',
      default => 'Normal Priority'
    };
  }

  /**
   * Scope for active firms
   */
  public function scopeActive(Builder $query): Builder
  {
    return $query->where('status', 'active');
  }

  /**
   * Scope for inactive firms
   */
  public function scopeInactive(Builder $query): Builder
  {
    return $query->where('status', 'inactive');
  }

  /**
   * Scope for prospect firms
   */
  public function scopeProspects(Builder $query): Builder
  {
    return $query->where('status', 'prospect');
  }

  /**
   * Scope for firms by industry
   */
  public function scopeByIndustry(Builder $query, string $industry): Builder
  {
    return $query->where('industry', $industry);
  }

  /**
   * Scope for firms by size
   */
  public function scopeBySize(Builder $query, string $size): Builder
  {
    return $query->where('size', $size);
  }

  /**
   * Scope for search
   */
  public function scopeSearch(Builder $query, string $search): Builder
  {
    return $query->where(function (Builder $q) use ($search) {
      $q->where('name', 'like', "%{$search}%")
        ->orWhere('slogan', 'like', "%{$search}%")
        ->orWhere('description', 'like', "%{$search}%")
        ->orWhere('industry', 'like', "%{$search}%")
        ->orWhereHas('address', function (Builder $addressQuery) use ($search) {
          $addressQuery->where('city', 'like', "%{$search}%")
            ->orWhere('state', 'like', "%{$search}%")
            ->orWhere('country', 'like', "%{$search}%");
        })
        ->orWhereHas('emails', function (Builder $emailQuery) use ($search) {
          $emailQuery->where('email', 'like', "%{$search}%");
        })
        ->orWhereHas('tags', function (Builder $tagQuery) use ($search) {
          $tagQuery->where('name', 'like', "%{$search}%");
        });
    });
  }

  /**
   * Get firms with contact and project counts
   */
  public function scopeWithCounts(Builder $query): Builder
  {
    return $query->withCount(['contacts', 'projects'])
      ->selectRaw('firms.*,
                        (SELECT COUNT(*) FROM projects
                         INNER JOIN contacts ON projects.contact_id = contacts.uuid
                         WHERE contacts.firm_id = firms.uuid AND projects.status = "active") as active_projects_count')
      ->selectRaw('(SELECT MAX(interaction_date) FROM interactions
                                 INNER JOIN contacts ON interactions.contact_id = contacts.uuid
                                 WHERE contacts.firm_id = firms.uuid) as last_interaction_date');
  }

  /**
   * Boot method for model events
   */
  protected static function boot(): void
  {
    parent::boot();

    static::deleting(function ($firm) {
      $firm->load('contacts.projects.tasks', 'tags', 'emails', 'phones', 'address');

      // Delete related contacts and their projects/tasks
      $firm->contacts->each(function ($contact) {
        $contact->projects->each(function ($project) {
          $project->tasks()->delete();
          $project->delete();
        });
        $contact->delete();
      });

      // Delete related data
      $firm->emails()->delete();
      $firm->phones()->delete();
      $firm->address()->delete();
      $firm->tags()->detach();

      // Delete media files
      $firm->clearMediaCollection('logo');
      $firm->clearMediaCollection('documents');
      $firm->clearMediaCollection('images');
    });

    static::creating(function ($firm) {
      // Set default status if not provided
      if (empty($firm->status)) {
        $firm->status = 'prospect';
      }

      // Set default priority if not provided
      if (empty($firm->priority)) {
        $firm->priority = 'medium';
      }
    });
  }

  /**
   * Add primary email to firm
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
   * Add primary phone to firm
   */
  public function addPrimaryPhone(string $phone, string $countryCode = '+1', string $type = 'business'): Phone
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
   * Update or create address
   */
  public function updateAddress(array $addressData): Address
  {
    return $this->address()->updateOrCreate(
      ['addressable_id' => $this->uuid, 'addressable_type' => self::class],
      array_merge($addressData, ['type' => 'primary'])
    );
  }

  /**
   * Get revenue formatted
   */
  public function getFormattedRevenueAttribute(): string
  {
    if (!$this->total_revenue) {
      return '$0';
    }

    return '$' . number_format($this->total_revenue, 0);
  }

  /**
   * Check if firm has active projects
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
   * Helper method to upload logo
   */
  public function uploadLogo($file): Media
  {
    return $this->addMediaFromRequest('logo')
      ->toMediaCollection('logo');
  }

  /**
   * Helper method to get logo with fallback
   */
  public function getLogoWithFallback(): string
  {
    return $this->getFirstMediaUrl('logo') ?: $this->generateFallbackLogo();
  }

  /**
   * Generate fallback logo URL (could be initials-based or default)
   */
  private function generateFallbackLogo(): string
  {
    // You could generate a URL to a service like UI Avatars or return a default logo
    $initials = $this->getInitials();
    return "https://ui-avatars.com/api/?name={$initials}&size=150&background=2563eb&color=ffffff&bold=true";
  }

  /**
   * Get firm initials for fallback logo
   */
  public function getInitials(): string
  {
    return collect(explode(' ', $this->name))
      ->map(fn($word) => strtoupper(substr($word, 0, 1)))
      ->take(2)
      ->join('');
  }

  /**
   * Upload document to firm
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
   * Upload image to firm
   */
  public function uploadImage($file, string $name = null): Media
  {
    $mediaAdder = $this->addMedia($file)
      ->toMediaCollection('images');

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
   * Get all images
   */
  public function getImages()
  {
    return $this->getMedia('images');
  }

  /**
   * Get firm's total project value
   */
  public function getTotalProjectValue(): float
  {
    return $this->projects()
      ->where('status', '!=', 'cancelled')
      ->sum('budget') ?? 0.0;
  }

  /**
   * Get firm's completion rate
   */
  public function getCompletionRate(): float
  {
    $totalProjects = $this->projects()->count();

    if ($totalProjects === 0) {
      return 0.0;
    }

    $completedProjects = $this->projects()
      ->where('status', 'completed')
      ->count();

    return round(($completedProjects / $totalProjects) * 100, 2);
  }

  /**
   * Get firm's activity score based on recent interactions
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
   * Check if firm is a high-value client
   */
  public function isHighValueClient(): bool
  {
    return $this->total_revenue && $this->total_revenue >= 50000;
  }

  /**
   * Get firm's primary contact
   */
  public function getPrimaryContact()
  {
    return $this->contacts()
      ->where('is_primary', true)
      ->first() ?? $this->contacts()->first();
  }

  /**
   * Get social media links
   */
  public function getSocialMediaLinks(): array
  {
    return array_filter([
      'linkedin' => $this->linkedin_url,
      'twitter' => $this->twitter_url,
      'facebook' => $this->facebook_url,
    ]);
  }

  /**
   * Update firm metadata
   */
  public function updateMetadata(array $metadata): void
  {
    $currentMetadata = $this->metadata ?? [];
    $this->update([
      'metadata' => array_merge($currentMetadata, $metadata)
    ]);
  }

  /**
   * Add note to firm
   */
  public function addNote(string $note): void
  {
    $currentNotes = $this->notes ? $this->notes . "\n\n" : '';
    $timestamp = now()->format('Y-m-d H:i:s');
    $newNote = "[{$timestamp}] {$note}";

    $this->update([
      'notes' => $currentNotes . $newNote
    ]);
  }

  /**
   * Get firm size label
   */
  public function getSizeLabelAttribute(): string
  {
    return match($this->size) {
      'small' => 'Small (1-50 employees)',
      'medium' => 'Medium (51-200 employees)',
      'large' => 'Large (201-1000 employees)',
      'enterprise' => 'Enterprise (1000+ employees)',
      default => 'Unknown Size'
    };
  }

  /**
   * Scope for high-value clients
   */
  public function scopeHighValue(Builder $query, float $threshold = 50000): Builder
  {
    return $query->where('total_revenue', '>=', $threshold);
  }

  /**
   * Scope for recently active firms
   */
  public function scopeRecentlyActive(Builder $query, int $days = 30): Builder
  {
    return $query->whereHas('interactions', function (Builder $q) use ($days) {
      $q->where('interaction_date', '>=', now()->subDays($days));
    });
  }

  /**
   * Scope for firms with active projects
   */
  public function scopeWithActiveProjects(Builder $query): Builder
  {
    return $query->whereHas('projects', function (Builder $q) {
      $q->where('status', 'active');
    });
  }

  /**
   * Convert firm to array for API responses
   */
  public function toApiArray(): array
  {
    return [
      'uuid' => $this->uuid,
      'name' => $this->name,
      'slogan' => $this->slogan,
      'website' => $this->url,
      'description' => $this->description,
      'industry' => $this->industry,
      'size' => $this->size,
      'size_label' => $this->size_label,
      'status' => $this->status,
      'status_label' => $this->status_label,
      'priority' => $this->priority,
      'priority_label' => $this->priority_label,
      'logo_url' => $this->logo_url,
      'logo_thumbnail_url' => $this->logo_thumbnail_url,
      'address' => $this->address?->toArray(),
      'primary_email' => $this->primary_email,
      'primary_phone' => $this->primary_phone,
      'social_media' => $this->getSocialMediaLinks(),
      'total_revenue' => $this->total_revenue,
      'formatted_revenue' => $this->formatted_revenue,
      'contacts_count' => $this->contacts_count ?? 0,
      'projects_count' => $this->projects_count ?? 0,
      'active_projects_count' => $this->active_projects_count ?? 0,
      'completion_rate' => $this->getCompletionRate(),
      'activity_score' => $this->getActivityScore(),
      'is_high_value' => $this->isHighValueClient(),
      'created_at' => $this->created_at?->toISOString(),
      'updated_at' => $this->updated_at?->toISOString(),
    ];
  }
}
