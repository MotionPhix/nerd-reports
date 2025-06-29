<?php

namespace App\Services;

use App\Enums\InteractionType;
use App\Models\Contact;
use App\Models\Interaction;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InteractionService
{
  /**
   * Create a new interaction
   */
  public function createInteraction(array $data): Interaction
  {
    return DB::transaction(function () use ($data) {
      $interaction = Interaction::create([
        'contact_id' => $data['contact_id'],
        'project_id' => $data['project_id'] ?? null,
        'user_id' => $data['user_id'] ?? auth()->id(),
        'type' => $data['type'],
        'subject' => $data['subject'],
        'description' => $data['description'] ?? null,
        'notes' => $data['notes'] ?? null,
        'duration_minutes' => $data['duration_minutes'] ?? null,
        'interaction_date' => isset($data['interaction_date']) ?
          Carbon::parse($data['interaction_date']) : now(),
        'follow_up_required' => $data['follow_up_required'] ?? false,
        'follow_up_date' => isset($data['follow_up_date']) ?
          Carbon::parse($data['follow_up_date']) : null,
        'outcome' => $data['outcome'] ?? null,
        'location' => $data['location'] ?? null,
        'participants' => $data['participants'] ?? null,
        'metadata' => $data['metadata'] ?? null,
      ]);

      // Update contact's last interaction timestamp
      $contact = Contact::find($data['contact_id']);
      if ($contact) {
        $contact->update(['last_interaction' => $interaction->interaction_date]);
      }

      Log::info("Interaction created", [
        'interaction_id' => $interaction->uuid,
        'type' => $interaction->type->value,
        'contact_id' => $interaction->contact_id,
        'project_id' => $interaction->project_id,
      ]);

      return $interaction;
    });
  }

  /**
   * Update an existing interaction
   */
  public function updateInteraction(Interaction $interaction, array $data): Interaction
  {
    $interaction->update([
      'subject' => $data['subject'] ?? $interaction->subject,
      'description' => $data['description'] ?? $interaction->description,
      'notes' => $data['notes'] ?? $interaction->notes,
      'duration_minutes' => $data['duration_minutes'] ?? $interaction->duration_minutes,
      'interaction_date' => isset($data['interaction_date']) ?
        Carbon::parse($data['interaction_date']) : $interaction->interaction_date,
      'follow_up_required' => $data['follow_up_required'] ?? $interaction->follow_up_required,
      'follow_up_date' => isset($data['follow_up_date']) ?
        Carbon::parse($data['follow_up_date']) : $interaction->follow_up_date,
      'outcome' => $data['outcome'] ?? $interaction->outcome,
      'location' => $data['location'] ?? $interaction->location,
      'participants' => $data['participants'] ?? $interaction->participants,
      'metadata' => $data['metadata'] ?? $interaction->metadata,
    ]);

    Log::info("Interaction updated", [
      'interaction_id' => $interaction->uuid,
      'changes' => $interaction->getChanges(),
    ]);

    return $interaction;
  }

  /**
   * Get interactions for a user with filters
   */
  public function getUserInteractions(User $user, array $filters = []): Collection
  {
    $query = Interaction::where('user_id', $user->id)
      ->with(['contact.firm', 'project']);

    // Apply filters
    if (isset($filters['type'])) {
      $query->where('type', $filters['type']);
    }

    if (isset($filters['contact_id'])) {
      $query->where('contact_id', $filters['contact_id']);
    }

    if (isset($filters['project_id'])) {
      $query->where('project_id', $filters['project_id']);
    }

    if (isset($filters['date_from'])) {
      $query->where('interaction_date', '>=', Carbon::parse($filters['date_from']));
    }

    if (isset($filters['date_to'])) {
      $query->where('interaction_date', '<=', Carbon::parse($filters['date_to']));
    }

    if (isset($filters['follow_up_required']) && $filters['follow_up_required']) {
      $query->where('follow_up_required', true);
    }

    if (isset($filters['overdue_follow_up']) && $filters['overdue_follow_up']) {
      $query->overdue();
    }

    if (isset($filters['search'])) {
      $search = $filters['search'];
      $query->where(function ($q) use ($search) {
        $q->where('subject', 'like', "%{$search}%")
          ->orWhere('description', 'like', "%{$search}%")
          ->orWhere('notes', 'like', "%{$search}%");
      });
    }

    // Ordering
    $orderBy = $filters['order_by'] ?? 'interaction_date';
    $orderDirection = $filters['order_direction'] ?? 'desc';
    $query->orderBy($orderBy, $orderDirection);

    return $query->get();
  }

  /**
   * Get interactions for a contact
   */
  public function getContactInteractions(Contact $contact, array $filters = []): Collection
  {
    $query = Interaction::where('contact_id', $contact->uuid)
      ->with(['user', 'project']);

    // Apply date filters
    if (isset($filters['date_from'])) {
      $query->where('interaction_date', '>=', Carbon::parse($filters['date_from']));
    }

    if (isset($filters['date_to'])) {
      $query->where('interaction_date', '<=', Carbon::parse($filters['date_to']));
    }

    return $query->orderBy('interaction_date', 'desc')->get();
  }

  /**
   * Get interactions for a project
   */
  public function getProjectInteractions(Project $project, array $filters = []): Collection
  {
    $query = Interaction::where('project_id', $project->uuid)
      ->with(['user', 'contact']);

    // Apply date filters
    if (isset($filters['date_from'])) {
      $query->where('interaction_date', '>=', Carbon::parse($filters['date_from']));
    }

    if (isset($filters['date_to'])) {
      $query->where('interaction_date', '<=', Carbon::parse($filters['date_to']));
    }

    return $query->orderBy('interaction_date', 'desc')->get();
  }

  /**
   * Get overdue follow-ups
   */
  public function getOverdueFollowUps(User $user = null): Collection
  {
    $query = Interaction::overdue()->with(['contact.firm', 'project', 'user']);

    if ($user) {
      $query->where('user_id', $user->id);
    }

    return $query->orderBy('follow_up_date', 'asc')->get();
  }

  /**
   * Get follow-ups due today
   */
  public function getTodayFollowUps(User $user = null): Collection
  {
    $query = Interaction::dueToday()->with(['contact.firm', 'project', 'user']);

    if ($user) {
      $query->where('user_id', $user->id);
    }

    return $query->orderBy('follow_up_date', 'asc')->get();
  }

  /**
   * Get follow-ups due soon
   */
  public function getUpcomingFollowUps(User $user = null, int $days = 7): Collection
  {
    $query = Interaction::dueSoon($days)->with(['contact.firm', 'project', 'user']);

    if ($user) {
      $query->where('user_id', $user->id);
    }

    return $query->orderBy('follow_up_date', 'asc')->get();
  }

  /**
   * Mark follow-up as completed
   */
  public function completeFollowUp(Interaction $interaction, string $outcome = null): Interaction
  {
    $interaction->update([
      'follow_up_required' => false,
      'outcome' => $outcome ?? $interaction->outcome,
    ]);

    Log::info("Follow-up completed", [
      'interaction_id' => $interaction->uuid,
      'outcome' => $outcome,
    ]);

    return $interaction;
  }

  /**
   * Schedule a follow-up interaction
   */
  public function scheduleFollowUp(Interaction $originalInteraction, array $data): Interaction
  {
    $followUpData = array_merge($data, [
      'contact_id' => $originalInteraction->contact_id,
      'project_id' => $originalInteraction->project_id,
      'user_id' => $data['user_id'] ?? $originalInteraction->user_id,
      'type' => $data['type'] ?? $originalInteraction->type,
      'subject' => $data['subject'] ?? "Follow-up: {$originalInteraction->subject}",
      'interaction_date' => Carbon::parse($data['interaction_date']),
      'metadata' => array_merge($data['metadata'] ?? [], [
        'original_interaction_id' => $originalInteraction->uuid,
        'is_follow_up' => true,
      ]),
    ]);

    $followUp = $this->createInteraction($followUpData);

    // Mark original interaction follow-up as scheduled
    $originalInteraction->update([
      'follow_up_required' => false,
      'metadata' => array_merge($originalInteraction->metadata ?? [], [
        'follow_up_scheduled' => true,
        'follow_up_interaction_id' => $followUp->uuid,
      ]),
    ]);

    return $followUp;
  }

  /**
   * Get interaction statistics for a user
   */
  public function getUserInteractionStats(User $user, Carbon $startDate = null, Carbon $endDate = null): array
  {
    $startDate = $startDate ?? now()->startOfMonth();
    $endDate = $endDate ?? now()->endOfMonth();

    $interactions = Interaction::where('user_id', $user->id)
      ->whereBetween('interaction_date', [$startDate, $endDate])
      ->get();

    $totalDuration = $interactions->sum('duration_minutes');
    $averageDuration = $interactions->count() > 0 ?
      round($totalDuration / $interactions->count(), 1) : 0;

    return [
      'total_interactions' => $interactions->count(),
      'total_duration_minutes' => $totalDuration,
      'total_duration_hours' => round($totalDuration / 60, 1),
      'average_duration_minutes' => $averageDuration,
      'unique_contacts' => $interactions->pluck('contact_id')->unique()->count(),
      'unique_projects' => $interactions->whereNotNull('project_id')->pluck('project_id')->unique()->count(),
      'follow_ups_required' => $interactions->where('follow_up_required', true)->count(),
      'overdue_follow_ups' => Interaction::where('user_id', $user->id)->overdue()->count(),
      'type_breakdown' => $this->getTypeBreakdown($interactions),
      'daily_average' => round($interactions->count() / $startDate->diffInDays($endDate), 1),
    ];
  }

  /**
   * Get interaction type breakdown
   */
  private function getTypeBreakdown(Collection $interactions): array
  {
    $breakdown = [];

    foreach (InteractionType::cases() as $type) {
      $breakdown[$type->value] = $interactions->where('type', $type)->count();
    }

    return $breakdown;
  }

  /**
   * Get recent interactions for dashboard
   */
  public function getRecentInteractions(User $user, int $limit = 10): Collection
  {
    return Interaction::where('user_id', $user->id)
      ->with(['contact.firm', 'project'])
      ->orderBy('interaction_date', 'desc')
      ->limit($limit)
      ->get();
  }

  /**
   * Get interaction summary for a date range
   */
  public function getInteractionSummary(User $user, Carbon $startDate, Carbon $endDate): array
  {
    $interactions = $this->getUserInteractions($user, [
      'date_from' => $startDate,
      'date_to' => $endDate,
    ]);

    $summary = [
      'total_interactions' => $interactions->count(),
      'total_time' => $interactions->sum('duration_minutes'),
      'contacts_contacted' => $interactions->pluck('contact_id')->unique()->count(),
      'projects_discussed' => $interactions->whereNotNull('project_id')->pluck('project_id')->unique()->count(),
      'follow_ups_created' => $interactions->where('follow_up_required', true)->count(),
    ];

    // Group by type
    foreach (InteractionType::cases() as $type) {
      $typeInteractions = $interactions->where('type', $type);
      $summary['by_type'][$type->value] = [
        'count' => $typeInteractions->count(),
        'total_time' => $typeInteractions->sum('duration_minutes'),
      ];
    }

    return $summary;
  }
}
