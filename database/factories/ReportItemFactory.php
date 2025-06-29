<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Report;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReportItem>
 */
class ReportItemFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $taskCount = fake()->numberBetween(1, 15);
    $completedTaskCount = fake()->numberBetween(0, $taskCount);

    return [
      'report_id' => Report::factory(),
      'project_id' => Project::factory(),
      'project_name' => fake()->words(3, true),
      'contact_name' => fake()->name(),
      'firm_name' => fake()->company(),
      'total_hours' => fake()->randomFloat(2, 0, 40),
      'task_count' => $taskCount,
      'completed_task_count' => $completedTaskCount,
      'tasks_data' => $this->generateTasksData($taskCount, $completedTaskCount),
      'notes' => fake()->boolean(40) ? fake()->paragraph() : null,
    ];
  }

  /**
   * Generate realistic tasks data
   */
  private function generateTasksData(int $taskCount, int $completedTaskCount): array
  {
    $tasks = [];
    $statuses = ['todo', 'in_progress', 'completed', 'review'];

    for ($i = 0; $i < $taskCount; $i++) {
      $isCompleted = $i < $completedTaskCount;

      $tasks[] = [
        'id' => fake()->uuid(),
        'name' => fake()->sentence(4),
        'description' => fake()->paragraph(),
        'status' => $isCompleted ? 'completed' : fake()->randomElement(['todo', 'in_progress', 'review']),
        'priority' => fake()->randomElement(['low', 'medium', 'high', 'urgent']),
        'estimated_hours' => fake()->randomFloat(2, 0.5, 8),
        'actual_hours' => $isCompleted ? fake()->randomFloat(2, 0.5, 10) : null,
        'started_at' => fake()->dateTimeBetween('-2 weeks', 'now'),
        'completed_at' => $isCompleted ? fake()->dateTimeBetween('-1 week', 'now') : null,
        'assigned_to' => fake()->name(),
      ];
    }

    return $tasks;
  }

  /**
   * Create a report item with high completion rate
   */
  public function highCompletion(): static
  {
    return $this->state(function (array $attributes) {
      $taskCount = fake()->numberBetween(5, 15);
      $completedTaskCount = fake()->numberBetween(
        (int)($taskCount * 0.8), // At least 80% completed
        $taskCount
      );

      return [
        'task_count' => $taskCount,
        'completed_task_count' => $completedTaskCount,
        'total_hours' => fake()->randomFloat(2, 20, 40),
        'tasks_data' => $this->generateTasksData($taskCount, $completedTaskCount),
      ];
    });
  }

  /**
   * Create a report item with low completion rate
   */
  public function lowCompletion(): static
  {
    return $this->state(function (array $attributes) {
      $taskCount = fake()->numberBetween(5, 15);
      $completedTaskCount = fake()->numberBetween(
        0,
        (int)($taskCount * 0.3) // At most 30% completed
      );

      return [
        'task_count' => $taskCount,
        'completed_task_count' => $completedTaskCount,
        'total_hours' => fake()->randomFloat(2, 5, 20),
        'tasks_data' => $this->generateTasksData($taskCount, $completedTaskCount),
      ];
    });
  }

  /**
   * Create a report item with no tasks
   */
  public function noTasks(): static
  {
    return $this->state(fn(array $attributes) => [
      'task_count' => 0,
      'completed_task_count' => 0,
      'total_hours' => 0,
      'tasks_data' => [],
    ]);
  }
}
