<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFirmRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return true; // You can add authorization logic here if needed
  }

  /**
   * Get the validation rules that apply to the request.
   */
  public function rules(): array
  {
    $firmUuid = $this->route('uuid');

    return [
      'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('firms', 'name')->ignore($firmUuid, 'uuid')
      ],
      'slogan' => [
        'nullable',
        'string',
        'max:500'
      ],
      'url' => [
        'nullable',
        'url',
        'max:255'
      ],
      'status' => [
        'required',
        'string',
        Rule::in(['active', 'inactive', 'prospect'])
      ],
      'industry' => [
        'nullable',
        'string',
        'max:100'
      ],
      'size' => [
        'nullable',
        'string',
        Rule::in(['small', 'medium', 'large', 'enterprise'])
      ],
      'description' => [
        'nullable',
        'string',
        'max:1000'
      ],

      // Address fields
      'address' => [
        'nullable',
        'array'
      ],
      'address.street' => [
        'nullable',
        'string',
        'max:255'
      ],
      'address.city' => [
        'nullable',
        'string',
        'max:100'
      ],
      'address.state' => [
        'nullable',
        'string',
        'max:100'
      ],
      'address.country' => [
        'nullable',
        'string',
        'max:100'
      ],
      'address.postal_code' => [
        'nullable',
        'string',
        'max:20'
      ],

      // Contact information
      'email' => [
        'nullable',
        'email',
        'max:255'
      ],
      'phone' => [
        'nullable',
        'string',
        'max:20'
      ],

      // Tags
      'tags' => [
        'nullable',
        'array'
      ],
      'tags.*' => [
        'string',
        'max:50'
      ],

      // Social media and additional URLs
      'linkedin_url' => [
        'nullable',
        'url',
        'max:255'
      ],
      'twitter_url' => [
        'nullable',
        'url',
        'max:255'
      ],
      'facebook_url' => [
        'nullable',
        'url',
        'max:255'
      ],

      // Metadata
      'notes' => [
        'nullable',
        'string',
        'max:2000'
      ],
      'priority' => [
        'nullable',
        'string',
        Rule::in(['low', 'medium', 'high'])
      ],
      'source' => [
        'nullable',
        'string',
        'max:100'
      ],
      'assigned_to' => [
        'nullable',
        'exists:users,id'
      ]
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'name.required' => 'The firm name is required.',
      'name.unique' => 'A firm with this name already exists.',
      'name.max' => 'The firm name cannot exceed 255 characters.',
      'url.url' => 'Please enter a valid website URL.',
      'status.required' => 'Please select a status for the firm.',
      'status.in' => 'The selected status is invalid.',
      'size.in' => 'The selected size is invalid.',
      'email.email' => 'Please enter a valid email address.',
      'linkedin_url.url' => 'Please enter a valid LinkedIn URL.',
      'twitter_url.url' => 'Please enter a valid Twitter URL.',
      'facebook_url.url' => 'Please enter a valid Facebook URL.',
      'priority.in' => 'The selected priority is invalid.',
      'assigned_to.exists' => 'The selected user does not exist.',
    ];
  }

  /**
   * Get custom attributes for validator errors.
   */
  public function attributes(): array
  {
    return [
      'name' => 'firm name',
      'url' => 'website URL',
      'address.street' => 'street address',
      'address.city' => 'city',
      'address.state' => 'state/province',
      'address.country' => 'country',
      'address.postal_code' => 'postal code',
      'linkedin_url' => 'LinkedIn URL',
      'twitter_url' => 'Twitter URL',
      'facebook_url' => 'Facebook URL',
    ];
  }

  /**
   * Prepare the data for validation.
   */
  protected function prepareForValidation(): void
  {
    // Clean up URL fields
    if ($this->has('url') && $this->url) {
      $this->merge([
        'url' => $this->ensureUrlHasProtocol($this->url)
      ]);
    }

    if ($this->has('linkedin_url') && $this->linkedin_url) {
      $this->merge([
        'linkedin_url' => $this->ensureUrlHasProtocol($this->linkedin_url)
      ]);
    }

    if ($this->has('twitter_url') && $this->twitter_url) {
      $this->merge([
        'twitter_url' => $this->ensureUrlHasProtocol($this->twitter_url)
      ]);
    }

    if ($this->has('facebook_url') && $this->facebook_url) {
      $this->merge([
        'facebook_url' => $this->ensureUrlHasProtocol($this->facebook_url)
      ]);
    }

    // Clean up phone number
    if ($this->has('phone') && $this->phone) {
      $this->merge([
        'phone' => preg_replace('/[^\d+\-\(\)\s]/', '', $this->phone)
      ]);
    }

    // Trim string fields
    $stringFields = ['name', 'slogan', 'description', 'industry', 'notes', 'source'];
    foreach ($stringFields as $field) {
      if ($this->has($field) && is_string($this->$field)) {
        $this->merge([
          $field => trim($this->$field)
        ]);
      }
    }
  }

  /**
   * Ensure URL has a protocol
   */
  private function ensureUrlHasProtocol(string $url): string
  {
    if (!preg_match('/^https?:\/\//', $url)) {
      return 'https://' . $url;
    }
    return $url;
  }
}
