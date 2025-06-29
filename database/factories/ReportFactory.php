<?php

namespace Database\Factories;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $startDate = fake()->dateTimeBetween('-3 months', 'now');
    $endDate = Carbon::parse($startDate)->addDays(6); // Weekly report by default
    $weekNumber = Carbon::parse($startDate)->weekOfYear;
    $year = Carbon::parse($startDate)->year;
    $month = Carbon::parse($startDate)->format('F');

    return [
      'title' => "Weekly Report - Week {$weekNumber}, {$year}",
      'description' => fake()->sentence(),
      'report_type' => ReportType::WEEKLY,
      'status' => fake()->randomElement(ReportStatus::cases()),
      'week_number' => $weekNumber,
      'year' => $year,
      'month' => $month,
      'start_date' => $startDate,
      'end_date' => $endDate,
      'generated_by' => User::factory(),
      'generated_at' => fake()->boolean(70) ? fake()->dateTimeBetween($startDate, 'now') : null,
      'sent_at' => fake()->boolean(50) ? fake()->dateTimeBetween($startDate, 'now') : null,
      'total_hours' => fake()->randomFloat(2, 0, 80),
      'total_tasks' => fake()->numberBetween(0, 50),
      'completed_tasks' => fake()->numberBetween(0, 30),
      'metadata' => $this->getMetadata(),
    ];
  }

  /**
   * Get metadata for the report
   */
  private function getMetadata(): array
  {
    return [
      'projects_count' => fake()->numberBetween(1, 10),
      'clients_count' => fake()->numberBetween(1, 5),
      'average_task_completion_time' => fake()->randomFloat(2, 0.5, 8),
      'productivity_score' => fake()->randomFloat(1, 60, 100),
      'notes' => fake()->boolean(30) ? fake()->sentence() : null,
    ];
  }

  /**
   * Create a weekly report
   */
  public function weekly(): static
  {
    return $this->state(function (array $attributes) {
      $startDate = fake()->dateTimeBetween('-3 months', 'now');
      $startOfWeek = Carbon::parse($startDate)->startOfWeek();
      $endOfWeek = Carbon::parse($startDate)->endOfWeek();

      return [
        'report_type' => ReportType::WEEKLY,
        'title' => "Weekly Report - Week {$startOfWeek->weekOfYear}, {$startOfWeek->year}",
        'start_date' => $startOfWeek,
        'end_date' => $endOfWeek,
        'week_number' => $startOfWeek->weekOfYear,
        'year' => $startOfWeek->year,
        'month' => $startOfWeek->format('F'),
      ];
    });
  }

  /**
   * Create a custom date range report
   */
  public function custom(): static
  {
    return $this->state(function (array $attributes) {
      $startDate = fake()->dateTimeBetween('-2 months', '-1 week');
      $endDate = fake()->dateTimeBetween($startDate, 'now');

      return [
        'report_type' => ReportType::CUSTOM,
        'title' => "Custom Report - " . Carbon::parse($startDate)->format('M j') . " to " . Carbon::parse($endDate)->format('M j, Y'),
        'start_date' => $startDate,
        'end_date' => $endDate,
        'week_number' => null,
        'year' => Carbon::parse($startDate)->year,
        'month' => Carbon::parse($startDate)->format('F'),
      ];
    });
  }

  /**
   * Create a monthly report
   */
  public function monthly(): static
  {
    return $this->state(function (array $attributes) {
      $date = fake()->dateTimeBetween('-6 months', 'now');
      $startOfMonth = Carbon::parse($date)->startOfMonth();
      $endOfMonth = Carbon::parse($date)->endOfMonth();

      return [
        'report_type' => ReportType::MONTHLY,
        'title' => "Monthly Report - " . $startOfMonth->format('F Y'),
        'start_date' => $startOfMonth,
        'end_date' => $endOfMonth,
        'week_number' => null,
        'year' => $startOfMonth->year,
        'month' => $startOfMonth->format('F'),
      ];
    });
  }

  /**
   * Create a generated report
   */
  public function generated(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => ReportStatus::GENERATED,
      'generated_at' => fake()->dateTimeBetween('-1 month', 'now'),
    ]);
  }

  /**
   * Create a sent report
   */
  public function sent(): static
  {
    return $this->state(function (array $attributes) {
      $generatedAt = fake()->dateTimeBetween('-1 month', 'now');

      return [
        'status' => ReportStatus::SENT,
        'generated_at' => $generatedAt,
        'sent_at' => fake()->dateTimeBetween($generatedAt, 'now'),
      ];
    });
  }

  /**
   * Create a draft report
   */
  public function draft(): static
  {
    return $this->state(fn(array $attributes) => [
      'status' => ReportStatus::DRAFT,
      'generated_at' => null,
      'sent_at' => null,
    ]);
  }
}
