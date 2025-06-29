<?php

namespace Database\Factories;

use App\Enums\InteractionType;
use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Interaction>
 */
class InteractionFactory extends Factory
{
  /**
   * Define the model's default state.
   *
   * @return array<string, mixed>
   */
  public function definition(): array
  {
    $interactionDate = fake()->dateTimeBetween('-6 months', 'now');
    $followUpRequired = fake()->boolean(30); // 30% chance of follow-up required

    return [
      'contact_id' => Contact::factory(),
      'project_id' => fake()->boolean(80) ? Project::factory() : null, // 80% chance of being linked to a project
      'user_id' => User::factory(),
      'type' => fake()->randomElement(InteractionType::cases()),
      'subject' => fake()->sentence(4),
      'description' => fake()->paragraphs(2, true),
      'notes' => fake()->boolean(60) ? fake()->paragraph() : null,
      'duration_minutes' => $this->getDurationByType(),
      'interaction_date' => $interactionDate,
      'follow_up_required' => $followUpRequired,
      'follow_up_date' => $followUpRequired ? fake()->dateTimeBetween($interactionDate, '+2 weeks') : null,
      'outcome' => fake()->randomElement([
        'Positive response',
        'Needs more information',
        'Will get back to us',
        'Approved to proceed',
        'Requires revision',
        'Meeting scheduled',
        'Issue resolved',
        'Pending decision',
        null
      ]),
      'location' => $this->getLocationByType(),
      'participants' => $this->getParticipants(),
      'metadata' => $this->getMetadata(),
    ];
  }

  /**
   * Get duration based on interaction type
   */
  private function getDurationByType(): ?int
  {
    return match (fake()->randomElement(InteractionType::cases())) {
      InteractionType::PHONE_CALL => fake()->numberBetween(5, 60),
      InteractionType::MEETING => fake()->numberBetween(30, 120),
      InteractionType::VIDEO_CALL => fake()->numberBetween(15, 90),
      InteractionType::IN_PERSON => fake()->numberBetween(30, 180),
      default => fake()->boolean(50) ? fake()->numberBetween(5, 30) : null,
    };
  }

  /**
   * Get location based on interaction type
   */
  private function getLocationByType(): ?string
  {
    $type = fake()->randomElement(InteractionType::cases());

    return match ($type) {
      InteractionType::IN_PERSON => fake()->randomElement([
        'Client Office',
        'Our Office',
        'Coffee Shop',
        'Restaurant',
        'Conference Room A',
        'Site Visit'
      ]),
      InteractionType::MEETING => fake()->randomElement([
        'Conference Room B',
        'Boardroom',
        'Client Office',
        'Zoom Meeting',
        'Teams Meeting'
      ]),
      InteractionType::VIDEO_CALL => fake()->randomElement([
        'Zoom',
        'Microsoft Teams',
        'Google Meet',
        'Skype'
      ]),
      default => null,
    };
  }

  /**
   * Get participants array
   */
  private function getParticipants(): ?array
  {
    if (fake()->boolean(40)) { // 40% chance of having additional participants
      return fake()->randomElements([
        'John Smith - Project Manager',
        'Sarah Johnson - Designer',
        'Mike Wilson - Developer',
        'Lisa Brown - Client Representative',
        'David Lee - Technical Lead',
        'Emma Davis - Business Analyst'
      ], fake()->numberBetween(1, 3));
    }

    return null;
  }

  /**
   * Get metadata based on interaction type
   */
  private function getMetadata(): ?array
  {
    if (fake()->boolean(30)) { // 30% chance of having metadata
      return [
        'priority' => fake()->randomElement(['low', 'medium', 'high']),
        'tags' => fake()->randomElements(['urgent', 'follow-up', 'decision-pending', 'approved'], fake()->numberBetween(0, 2)),
        'reference_number' => fake()->bothify('REF-####-??'),
        'attachments_count' => fake()->numberBetween(0, 5),
      ];
    }

    return null;
  }

  /**
   * Create a phone call interaction
   */
  public function phoneCall(): static
  {
    return $this->state(fn(array $attributes) => [
      'type' => InteractionType::PHONE_CALL,
      'duration_minutes' => fake()->numberBetween(5, 60),
      'location' => null,
    ]);
  }

  /**
   * Create an email interaction
   */
  public function email(): static
  {
    return $this->state(fn(array $attributes) => [
      'type' => InteractionType::EMAIL,
      'duration_minutes' => null,
      'location' => null,
    ]);
  }

  /**
   * Create a meeting interaction
   */
  public function meeting(): static
  {
    return $this->state(fn(array $attributes) => [
      'type' => InteractionType::MEETING,
      'duration_minutes' => fake()->numberBetween(30, 120),
      'location' => fake()->randomElement(['Conference Room A', 'Boardroom', 'Client Office']),
    ]);
  }

  /**
   * Create an interaction requiring follow-up
   */
  public function requiresFollowUp(): static
  {
    return $this->state(fn(array $attributes) => [
      'follow_up_required' => true,
      'follow_up_date' => fake()->dateTimeBetween('now', '+2 weeks'),
    ]);
  }

  /**
   * Create an overdue follow-up interaction
   */
  public function overdueFollowUp(): static
  {
    return $this->state(fn(array $attributes) => [
      'follow_up_required' => true,
      'follow_up_date' => fake()->dateTimeBetween('-1 week', '-1 day'),
    ]);
  }
}
