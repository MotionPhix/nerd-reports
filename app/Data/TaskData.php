<?php

namespace App\Data;

use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class TaskData extends Data
{
  public function __construct(

    public string|null|Optional $tid,

    public string|null|Optional $id,

    public string $name,

    public string $priority,

    public string|null|Optional $created_at,

    public string|null|Optional $description,

    public string|null|Optional $status,

    public UserData|Optional $user,

    public UserData|Optional $assignee,

    public int|Optional $comments_count,

    public int|Optional $files_count,

    public int $board_id,

    /** @var Collection<CommentData> */
    public Collection|null|Optional $comments,

    public int|null|Optional $position,

    public int $assigned_to,

    public int|null|Optional $assigned_by

  ) {}

  public static function rules(): array
  {
    return [
      'name' => [
        'required',
        'min:5'
      ],

      'description' => 'sometimes|min:20',

      'assigned_to' => ['required', 'exists:users,id'],

      'priority' => [
        'sometimes',
        Rule::in(['normal', 'medium', 'high'])
      ],
    ];
  }

  public static function messages()
  {
    return [
      'name.required' => 'Provide context for the task',
      'name.min' => 'The name should at least be :min characters long',

      'description.min' => 'Be a bit verbose. Describe the task better',

      'assigned_to.required' => 'Pick a person to work on the task',
      'assigned_to.exists' => 'The assigned person cannot be found',

      'priority.in' => 'The priority value must be one of :values',
    ];
  }
}
