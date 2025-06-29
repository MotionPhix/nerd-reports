<?php

namespace App\Models;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Traits\HasUuid;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Report extends Model implements HasMedia
{
  use HasFactory, HasUuid, InteractsWithMedia;

  protected $fillable = [
    'title',
    'description',
    'report_type',
    'status',
    'week_number',
    'year',
    'month',
    'start_date',
    'end_date',
    'generated_by',
    'generated_at',
    'sent_at',
    'total_hours',
    'total_tasks',
    'completed_tasks',
    'metadata'
  ];

  protected function casts(): array
  {
    return [
      'start_date' => 'date',
      'end_date' => 'date',
      'generated_at' => 'datetime',
      'sent_at' => 'datetime',
      'report_type' => ReportType::class,
      'status' => ReportStatus::class,
      'total_hours' => 'decimal:2',
      'metadata' => 'array',
    ];
  }

  public function generator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'generated_by');
  }

  public function reportItems(): HasMany
  {
    return $this->hasMany(ReportItem::class);
  }

  public function reportRecipients(): HasMany
  {
    return $this->hasMany(ReportRecipient::class);
  }

  // Helper methods for report generation
  public function getWeekRange(): string
  {
    return $this->start_date->format('M j') . ' - ' . $this->end_date->format('M j, Y');
  }

  public function getWeekDescription(): string
  {
    return "Week {$this->week_number} of {$this->year} ({$this->getWeekRange()})";
  }

  public function addProject(Project $project, array $tasks = []): void
  {
    $reportItem = $this->reportItems()->create([
      'project_id' => $project->uuid,
      'project_name' => $project->name,
      'contact_name' => $project->contact->full_name ?? null,
      'firm_name' => $project->contact->firm->name ?? null,
      'total_hours' => collect($tasks)->sum('actual_hours'),
      'task_count' => count($tasks),
      'completed_task_count' => collect($tasks)->where('status', 'completed')->count(),
      'tasks_data' => $tasks,
    ]);
  }

  public function markAsGenerated(): void
  {
    $this->update([
      'status' => ReportStatus::GENERATED,
      'generated_at' => now(),
    ]);
  }

  public function markAsSent(): void
  {
    $this->update([
      'status' => ReportStatus::SENT,
      'sent_at' => now(),
    ]);
  }

  public function scopeForWeek($query, int $year, int $week)
  {
    return $query->where('year', $year)->where('week_number', $week);
  }

  public function scopeForDateRange($query, Carbon $startDate, Carbon $endDate)
  {
    return $query->where('start_date', '>=', $startDate)
      ->where('end_date', '<=', $endDate);
  }

  public function scopeForUser($query, $userId)
  {
    return $query->where('generated_by', $userId);
  }

  public function scopeWeekly($query)
  {
    return $query->where('report_type', ReportType::WEEKLY);
  }

  public function scopeCustom($query)
  {
    return $query->where('report_type', ReportType::CUSTOM);
  }

  // Generate automatic title based on report type and date range
  protected function generateTitle(): string
  {
    if ($this->report_type === ReportType::WEEKLY) {
      return "Weekly Report - Week {$this->week_number}, {$this->year}";
    }

    return "Custom Report - {$this->start_date->format('M j')} to {$this->end_date->format('M j, Y')}";
  }

  protected static function boot()
  {
    parent::boot();

    static::creating(function ($report) {
      if (empty($report->title)) {
        $report->title = $report->generateTitle();
      }
    });
  }
}
