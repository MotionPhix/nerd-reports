<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Phone>
 */
class PhoneFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $phone_number = fake('ZA')->unique()->phoneNumber();

    return [
      'number' => $phone_number,
      'type' => fake()->randomElement(['mobile', 'work', 'home', 'fax']),
      'country_code' => fake('ZA')->countryCode(),
      'is_primary_phone' => fake()->boolean(70),
      'formatted' => $phone_number,
      'phoneable_type' => \App\Models\Contact::class,
    ];
  }
}
