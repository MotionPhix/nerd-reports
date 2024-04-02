<?php

namespace App\Data;

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
    $project_id = request()->route()->parameter('project')->id;
    $board_id = request()->route()->parameter('board')->id ?? null;

    return [
      'name' => [
        'required',
        'string',
        Rule::unique('boards')->where(function ($query) use ($project_id, $board_id) {
          return $query->where('project_id', $project_id)
            ->when($board_id, function ($query) use ($board_id) {
              return $query->where('id', '!=', $board_id);
            });
        })
      ],
    ];
  }
}
