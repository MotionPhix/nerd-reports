<?php

namespace App\Data;

use App\Enums\AddressType;
use App\Enums\PhoneType;
use App\Enums\Title;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;


/**
 * @typescript
 */
class ContactFullData extends Data
{
  public function __construct(

    public int|Optional $id,

    public string|Optional $cid,

    public string $first_name,

    public string $last_name,

    public string|null|Optional $nickname,

    public string|null|Optional $middle_name,

    public string|null|Optional $job_title,

    public string|null|Optional $bio,

    /** @var Collection<PhoneData> */
    public Collection $phones,

    /** @var Collection<EmailData> */
    public Collection $emails,

    public FirmData|null|Optional $firm,

    public string $title = Title::DR->value,

  ) {}

  public static function rules(): array
  {
    $contactId = (request()->route()->parameters())['contact']->id ?? null;

    return [
      'first_name' => 'required',
      'last_name' => 'required',
      'title' => ['sometimes', Rule::enum(Title::class)],
      'middle_name' => 'sometimes|filled',
      'nickname' => 'sometimes|filled',
      'job_title' => 'sometimes|filled',
      'bio' => 'sometimes|filled',

      'firm.fid' => 'sometimes|exists:firms,fid',
      'firm.name' => 'required_if:firm.fid,null',
      'firm.url' => 'sometimes|filled|url:http,https',
      'firm.slogan' => 'sometimes|filled',
      'firm.address.type' => ['required_unless:firm.id,null', Rule::enum(AddressType::class)],
      'firm.address.street' => 'sometimes|required_unless:firm.id,null',
      'firm.address.city' => 'sometimes|required_unless:firm.street,null',
      'firm.address.state' => 'sometimes|filled',
      'firm.address.country' => 'sometimes|filled',

      'phones' => 'sometimes|array',
      'phones.*.type' => ['required', Rule::enum(PhoneType::class)],
      'phones.*.is_primary_phone' => 'required|boolean',
      'phones.*.number' => 'required|filled|phone:AUTO',
      'phones.*.country_code' => ['sometimes', 'filled', Rule::in(['MW', 'ZA', 'ZM', 'ZW'])],

      'emails' => 'sometimes|array',
      'emails.*.email' => ['required', 'email:rfc,dns', Rule::unique('emails', 'email')->ignore($contactId, 'emailable_id')],
    ];
  }

  public static function messages(): array
  {
    return [

      'first_name.required' => 'Enter first name',
      'last_name.required' => 'Enter surname',

      'firm.fid.exists' => 'Oops! We don\'t have that company yet!',
      'firm.url.filled' => 'You should fill out the company website',
      'firm.name.required_if' => 'Fill out the company\'s name',
      'firm.url.url' => 'You entered an invalid web URL.',
      'firm.address.type.required' => 'Address type is missing',
      'firm.address.type.enum' => 'Address type can only be "office" or "home"',
      'firm.address.street.required' => 'Provide a street name',
      'firm.address.city.required' => 'Provide city name',
      // 'firm.address.state.required' => 'Provide state/province name',
      // 'firm.address.country.required' => 'Provide country name',

      'emails.*.email.required' => 'Provide an email address',
      'emails.*.email.email' => 'You entered an invalid email',
      'emails.*.email.unique' => 'This email is already taken',

      'phones.*.type.in' => 'Invalid phone type',
      'phones.*.country_code.filled' => 'The country code is not set',
      'phones.*.country_code.in' => 'Oops! No support for this country yet',
      'phones.*.number.required' => 'Fill out a phone number',
    ];
  }
}
