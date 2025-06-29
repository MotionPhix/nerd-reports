<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Enums\TaskPriority;
use App\Models\Board;
use App\Models\Project;
use App\Models\User;
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
    $status = fake()->randomElement(TaskStatus::cases());
    $createdAt = fake()->dateTimeBetween('-3 months', 'now');
    $estimatedHours = fake()->randomFloat(2, 0.5, 16);

    // Generate realistic dates based on status
    $startedAt = null;
    $completedAt = null;
    $actualHours = null;

    if (in_array($status, [TaskStatus::IN_PROGRESS, TaskStatus::COMPLETED, TaskStatus::REVIEW])) {
      $startedAt = fake()->dateTimeBetween($createdAt, 'now');
    }

    if ($status === TaskStatus::COMPLETED) {
      $completedAt = fake()->dateTimeBetween($startedAt ?? $createdAt, 'now');
      $actualHours = fake()->randomFloat(2, $estimatedHours * 0.5, $estimatedHours * 1.5);
    } elseif ($status === TaskStatus::IN_PROGRESS) {
      // Partial hours for in-progress tasks
      $actualHours = fake()->boolean(70) ? fake()->randomFloat(2, 0.5, $estimatedHours * 0.8) : null;
    }

    return [
      'name' => fake()->sentence(4),
      'description' => fake()->paragraph(),
      'status' => $status,
      'priority' => fake()->randomElement(TaskPriority::cases()),
      'estimated_hours' => $estimatedHours,
      'actual_hours' => $actualHours,
      'started_at' => $startedAt,
      'completed_at' => $completedAt,
      'due_date' => fake()->boolean(60) ? fake()->dateTimeBetween('now', '+2 months') : null,
      'notes' => fake()->boolean(30) ? fake()->paragraph() : null,
      'position' => fake()->randomFloat(2, 1000, 999999),
      'board_id' => Board::factory(),
      'project_id' => Project::factory(),
      'assigned_to' => User::factory(),
      'assigned_by' => User::factory(),
      'created_at' => $createdAt,
    ];
  }

  /**
   * Create a completed task
   */
  public function completed(): static
  {
    return $this->state(function (array $attributes) {
      $createdAt = $attributes['created_at'] ?? fake()->dateTimeBetween('-2 months', '-1 week');
      $startedAt = fake()->dateTimeBetween($createdAt, '-3 days');
      $completedAt = fake()->dateTimeBetween($startedAt, 'now');
      $estimatedHours = $attributes['estimated_hours'] ?? fake()->randomFloat(2, 1, 8);

      return [
        'status' => TaskStatus::COMPLETED,
        'started_at' => $startedAt,
        'completed_at' => $completedAt,
        'actual_hours' => fake()->randomFloat(2, $estimatedHours * 0.7, $estimatedHours * 1.3),
      ];
    });
  }

  /**
   * Create an in-progress task
   */
  public function inProgress(): static
  {
    return $this->state(function (array $attributes) {
      $createdAt = $attributes['created_at'] ?? fake()->dateTimeBetween('-1 month', 'now');
      $startedAt = fake()->dateTimeBetween($createdAt, 'now');
      $estimatedHours = $attributes['estimated_hours'] ?? fake()->randomFloat(2, 1, 8);

      return [
        'status' => TaskStatus::IN_PROGRESS,
        'started_at' => $startedAt,
        'completed_at' => null,
        'actual_hours' => fake()->boolean(70) ? fake()->randomFloat(2, 0.5, $estimatedHours * 0.8) : null,
      ];
    });
  }

  /**
   * Create a todo task
   */
  public function todo(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => TaskStatus::TODO,
      'started_at' => null,
      'completed_at' => null,
      'actual_hours' => null,
    ]);
  }

  /**
   * Create a high priority task
   */
  public function highPriority(): static
  {
    return $this->state(fn(array $attributes) => [
      'priority' => TaskPriority::HIGH,
    ]);
  }

  /**
   * Create an urgent task
   */
  public function urgent(): static
  {
    return $this->state(fn(array $attributes) => [
      'priority' => TaskPriority::URGENT,
      'due_date' => fake()->dateTimeBetween('now', '+1 week'),
    ]);
  }

  /**
   * Create an overdue task
   */
  public function overdue(): static
  {
    return $this->state(fn(array $attributes) => [
      'due_date' => fake()->dateTimeBetween('-2 weeks', '-1 day'),
      'status' => fake()->randomElement([TaskStatus::TODO, TaskStatus::IN_PROGRESS]),
    ]);
  }

  /**
   * Create a task completed this week
   */
  public function completedThisWeek(): static
  {
    return $this->state(function (array $attributes) {
      $startOfWeek = now()->startOfWeek();
      $endOfWeek = now()->endOfWeek();
      $completedAt = fake()->dateTimeBetween($startOfWeek, $endOfWeek);
      $startedAt = fake()->dateTimeBetween($startOfWeek, $completedAt);
      $estimatedHours = $attributes['estimated_hours'] ?? fake()->randomFloat(2, 1, 8);

      return [
        'status' => TaskStatus::COMPLETED,
        'started_at' => $startedAt,
        'completed_at' => $completedAt,
        'actual_hours' => fake()->randomFloat(2, $estimatedHours * 0.7, $estimatedHours * 1.3),
      ];
    });
  }
}
