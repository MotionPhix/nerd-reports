<?php

namespace App\Data;

use App\Enums\ProjectStatus;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class ProjectData extends Data
{
  public function __construct(

    public readonly string $pid,

    public readonly string $name,

    public readonly string|Optional $created_at,

    public readonly string $due_date,

    public readonly string $status,

    public ContactData $contact,

    public UserData $author,

  ) {}

  public function toArray(): array
  {
    return [
      'pid' => $this->pid,
      'name' => $this->name,
      'created_at' => $this->created_at,
      'due_date' => $this->due_date,
      'status' => ProjectStatus::tryFrom($this->status)->getLabel(),
      'contact' => $this->contact->toArray(),
      'author' => $this->author->toArray(),
    ];
  }
}
