<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'first_name' => fake()->firstName(),
      'last_name' => fake()->lastName(),
      'job_title' => fake()->jobTitle(),
      'title' => fake()->randomElement(['mr', 'mrs', 'ms', 'sr', 'dr', 'prof']),
      'middle_name' => fake()->firstName(),
      'nickname' => fake()->userName(),
      'bio' => fake()->paragraph(),
    ];
  }
}
