<?php

namespace Database\Factories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
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
      'tid' => Str::orderedUuid(),
      'status' => fake()->randomElement(['done', 'cancelled', 'in_progress']),
      'priority' => fake()->randomElement(['normal', 'medium', 'high']),
      'created_at' => Carbon::yesterday()->subDays(rand(0, 60)),
    ];
  }
}
