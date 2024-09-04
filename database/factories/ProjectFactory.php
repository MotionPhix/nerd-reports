<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    return [
      'name' => fake()->sentence(),
      'description' => fake()->paragraph(),
      'due_date' => fake()->dateTimeBetween('-2 months', '+2 months'),
      'status' => fake()->randomElement(['completed', 'in_progress', 'cancelled']),
    ];
  }
}
