<?php

namespace App\Models;

use App\Enums\ReportType;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportTemplate extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'name',
    'description',
    'report_type',
    'template_content',
    'email_subject_template',
    'email_body_template',
    'is_default',
    'is_active',
    'created_by',
    'settings'
  ];

  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $keyType = 'string';

  protected function casts(): array
  {
    return [
      'report_type' => ReportType::class,
      'is_default' => 'boolean',
      'is_active' => 'boolean',
      'settings' => 'array',
    ];
  }

  public function creator(): BelongsTo
  {
    return $this->belongsTo(User::class, 'created_by');
  }

  public function scopeActive($query)
  {
    return $query->where('is_active', true);
  }

  public function scopeDefault($query)
  {
    return $query->where('is_default', true);
  }

  public function scopeForType($query, ReportType $type)
  {
    return $query->where('report_type', $type);
  }

  public function getDefaultEmailSubject(array $variables = []): string
  {
    $subject = $this->email_subject_template ?? 'Weekly Report - Week {week_number}, {year}';

    foreach ($variables as $key => $value) {
      $subject = str_replace('{' . $key . '}', $value, $subject);
    }

    return $subject;
  }

  public function getDefaultEmailBody(array $variables = []): string
  {
    $body = $this->email_body_template ?? $this->getDefaultEmailBodyTemplate();

    foreach ($variables as $key => $value) {
      $body = str_replace('{' . $key . '}', $value, $body);
    }

    return $body;
  }

  private function getDefaultEmailBodyTemplate(): string
  {
    return "
Dear {recipient_name},

Please find attached my weekly report for {week_description}.

Summary:
- Total Projects Worked On: {total_projects}
- Total Tasks: {total_tasks}
- Completed Tasks: {completed_tasks}
- Total Hours: {total_hours}

Best regards,
{sender_name}
        ";
  }
}
