<?php

namespace App\Data;

use App\Enums\ProjectStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

/** @typescript * */
class ProjectFullData extends Data
{
  public function __construct(
    public string|null|Optional $pid,

    public string $name,

    public string|null|Optional $created_at,

    public string|null $due_date,

    public string|null|Optional $deadline,

    public string|Optional $status,

    public string|null|Optional $description,

    public string|int|null|Optional $contact_id,

    public ContactData|Optional $contact,

    /** @var Collection<BoardData> */
    public Collection|null|Optional $boards,
  ) {}

  public static function rules(): array
  {
    return [
      'name' => 'required|min:5',

      'status' => [
        Rule::requiredIf(function () {

          $url = request()->getPathInfo();
          $lastPart = Str::afterLast($url, '/');

          if (is_null($lastPart)) {

            return true;

          }

          return false;

        }),

        Rule::in(ProjectStatus::cases())
      ],

      'description' => 'sometimes|min:50',

      'contact_id' => [
        Rule::requiredIf(request()->method() === 'POST'),
        'exists:contacts,cid'
      ],

      'due_date' => [
        Rule::requiredIf(request()->method() === 'POST'),
        'date', 'after_or_equal:today'
      ],

      'documents.*' => 'sometimes|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx',
    ];
  }

  public static function messages()
  {
    return [
      'name.required' => 'Type in project\'s name',
      'name.min' => 'The name may not be less than :min characters',

      'description.min' => 'Provide more information for clarity; at least :min characters',

      'due_date.required' => 'Specify a deadline date for the project',
      'due_date.date' => 'The `due date` must be a valid dead',
      'due_date.after_or_equal' => 'The `due date` must be ahead of today\'s date',

      'contact_id.required' => 'Pick a contact person for the project',
      'contact_id.exists' => 'The contact couldn\'t be found',

      'documents.mimes' => 'The files must be of JPEG, PNG, GIF, PDF, DOC, DOCX, XLS, or XLSX type.',
    ];
  }
}
