<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportRecipient extends Model
{
  use HasFactory, HasUuid;

  protected $fillable = [
    'report_id',
    'contact_id',
    'email',
    'name',
    'sent_at',
    'delivery_status',
    'delivery_notes'
  ];

  protected $primaryKey = 'uuid';
  public $incrementing = false;
  protected $keyType = 'string';

  protected function casts(): array
  {
    return [
      'sent_at' => 'datetime',
    ];
  }

  public function report(): BelongsTo
  {
    return $this->belongsTo(Report::class);
  }

  public function contact(): BelongsTo
  {
    return $this->belongsTo(Contact::class);
  }

  public function markAsSent(): void
  {
    $this->update([
      'sent_at' => now(),
      'delivery_status' => 'sent'
    ]);
  }

  public function markAsDelivered(): void
  {
    $this->update([
      'delivery_status' => 'delivered'
    ]);
  }

  public function markAsFailed(string $reason = null): void
  {
    $this->update([
      'delivery_status' => 'failed',
      'delivery_notes' => $reason
    ]);
  }

  public function scopePending($query)
  {
    return $query->whereNull('sent_at');
  }

  public function scopeSent($query)
  {
    return $query->whereNotNull('sent_at');
  }

  public function scopeDelivered($query)
  {
    return $query->where('delivery_status', 'delivered');
  }

  public function scopeFailed($query)
  {
    return $query->where('delivery_status', 'failed');
  }
}
