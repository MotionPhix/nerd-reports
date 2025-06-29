<?php

namespace App\Models;

use App\Enums\InteractionType;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Stevebauman\Purify\Casts\PurifyHtmlOnGet;

class Interaction extends Model implements HasMedia
{
    use HasFactory, HasUuid, InteractsWithMedia;

    protected $fillable = [
        'contact_id',
        'project_id',
        'user_id',
        'type',
        'subject',
        'description',
        'notes',
        'duration_minutes',
        'interaction_date',
        'follow_up_required',
        'follow_up_date',
        'outcome',
        'location',
        'participants',
        'metadata'
    ];

    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected function casts(): array
    {
        return [
            'interaction_date' => 'datetime',
            'follow_up_date' => 'date',
            'follow_up_required' => 'boolean',
            'type' => InteractionType::class,
            'participants' => 'array',
            'metadata' => 'array',
            'description' => PurifyHtmlOnGet::class,
            'notes' => PurifyHtmlOnGet::class,
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id', 'uuid');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'uuid');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Helper methods
    public function getFormattedDuration(): string
    {
        if (!$this->duration_minutes) {
            return 'Duration not specified';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    public function isOverdue(): bool
    {
        return $this->follow_up_required &&
               $this->follow_up_date &&
               $this->follow_up_date->isPast();
    }

    public function isDueToday(): bool
    {
        return $this->follow_up_required &&
               $this->follow_up_date &&
               $this->follow_up_date->isToday();
    }

    public function isDueSoon(): bool
    {
        return $this->follow_up_required &&
               $this->follow_up_date &&
               $this->follow_up_date->isBetween(now(), now()->addDays(3));
    }

    // Scopes
    public function scopeForContact($query, $contactId)
    {
        return $query->where('contact_id', $contactId);
    }

    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeOfType($query, InteractionType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeRequiringFollowUp($query)
    {
        return $query->where('follow_up_required', true)
                    ->whereNotNull('follow_up_date');
    }

    public function scopeOverdue($query)
    {
        return $query->requiringFollowUp()
                    ->where('follow_up_date', '<', now());
    }

    public function scopeDueToday($query)
    {
        return $query->requiringFollowUp()
                    ->whereDate('follow_up_date', today());
    }

    public function scopeDueSoon($query, int $days = 3)
    {
        return $query->requiringFollowUp()
                    ->whereBetween('follow_up_date', [now(), now()->addDays($days)]);
    }

    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('interaction_date', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('interaction_date', '>=', now()->subDays($days));
    }

    // Attributes
    protected function interactionDate(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? Carbon::parse($value) : null,
        );
    }

    protected function formattedInteractionDate(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->interaction_date?->format('M j, Y g:i A'),
        );
    }

    protected function timeAgo(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->interaction_date?->diffForHumans(),
        );
    }
}
