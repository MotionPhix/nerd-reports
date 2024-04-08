<?php

namespace App\Rules;

use App\Models\Board;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueBoardName implements ValidationRule
{
  protected $projectId;

  public function __construct(int|null $projectId)
  {

    $this->projectId = $projectId;

  }

  /**
   * Determine if the validation rule passes.
   *
   * @param  string  $attribute
   * @param  mixed  $value
   * @return bool
   */
  public function validate(string $attribute, mixed $value, Closure $fail): void
  {
    $value = preg_replace('/[^a-zA-Z0-9]/', '', $value);

    $value = strtolower($value);

    $query = Board::where('project_id', $this->projectId)
        ->whereRaw('LOWER(name) = ?', [$value]);

    // $sql = $query->toSql();

    // Get the bindings array
    // $bindings = $query->getBindings();

    // Replace placeholders with actual values
    // $sqlWithBindings = vsprintf(str_replace('?', "'%s'", $sql), $bindings);

    // Log or output the raw SQL query with bindings
    // dd($sqlWithBindings);

    if ($query->exists()) {
      $fail('The board name must be unique within the project.');
    }
  }
}
