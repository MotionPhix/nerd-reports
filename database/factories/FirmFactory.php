<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Firm>
 */
class FirmFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => fake()->company(),
      'fid' => Str::orderedUuid(),
      'slogan' => fake()->sentence(),
      'url' => fake()->url(),
    ];
  }
}
