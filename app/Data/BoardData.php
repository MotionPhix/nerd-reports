<?php

namespace App\Data;

use App\Models\Board;
use App\Rules\UniqueBoardName;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript **/
class BoardData extends Data
{
  public function __construct(

    public int|null|Optional $id,

    public string $name,

    public int|Optional $project_id,

    /** @var Collection<TaskData> */
    public Collection|null|Optional $tasks,

  ) {}

  public static function rules()
  {
    $project_id = request()->route()->parameter('project')->id ?? null;

    return [
      'name' => [
        'required',
        'string',
        new UniqueBoardName($project_id),
      ],
    ];
  }
}
