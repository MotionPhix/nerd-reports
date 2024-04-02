<?php

namespace App\Data;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript * */
class TagData extends Data
{
  public function __construct(

    public int|Optional          $id,

    public string|array          $name,

    public string|array|Optional $slug,

    public string|Optional       $type,

    public int|Optional          $order_column

  ) {}

  public static function rules()
  {
    $tagId = (request()->route()->parameters())['tag']->id ?? null;

    return [
      'name' => [
        'required',
        Rule::unique('tags', 'slug')
          ->where(fn($query) => $query->where('slug', '!=', Str::slug(request()->request->get('name'))))
          ->ignore($tagId)
      ],
    ];
  }

  public static function messages()
  {
    return [
      'name.required' => 'Tag name cannot be empty',
      'name.unique' => 'A tag with a similar name already exists',
    ];
  }
}
