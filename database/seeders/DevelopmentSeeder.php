<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Contact;
use App\Models\Firm;
use App\Models\Interaction;
use App\Models\Project;
use App\Models\Report;
use App\Models\ReportItem;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevelopmentSeeder extends Seeder
{
  /**
   * Run the database seeder for development environment.
   */
  public function run(): void
  {
    $this->command->info('🌱 Seeding development data...');

    // Create users with roles
    $this->command->info('👥 Creating users...');

    $admin = User::factory()->admin()->create([
      'first_name' => 'Admin',
      'last_name' => 'User',
      'email' => 'admin@example.com',
    ]);

    $projectManager = User::factory()->projectManager()->create([
      'first_name' => 'Project',
      'last_name' => 'Manager',
      'email' => 'pm@example.com',
    ]);

    $teamMembers = User::factory()->teamMember()->count(3)->create();
    $regularUsers = User::factory()->count(5)->create();

    // Create firms
    $this->command->info('🏢 Creating firms...');
    $firms = Firm::factory()->count(8)->create();

    // Create contacts for each firm
    $this->command->info('👤 Creating contacts...');
    $contacts = collect();
    $firms->each(function ($firm) use (&$contacts) {
      $firmContacts = Contact::factory()
        ->count(rand(2, 5))
        ->create(['firm_id' => $firm->uuid]);
      $contacts = $contacts->merge($firmContacts);
    });

    // Create projects
    $this->command->info('📋 Creating projects...');
    $projects = collect();
    $contacts->each(function ($contact) use (&$projects, $admin, $projectManager) {
      if (fake()->boolean(70)) { // 70% chance each contact has a project
        $projectCount = fake()->numberBetween(1, 3);
        $contactProjects = Project::factory()
          ->count($projectCount)
          ->create([
            'contact_id' => $contact->uuid,
            'created_by' => fake()->randomElement([$admin->id, $projectManager->id]),
          ]);
        $projects = $projects->merge($contactProjects);
      }
    });

    // Create boards for projects
    $this->command->info('📊 Creating boards...');
    $boards = collect();
    $projects->each(function ($project) use (&$boards) {
      $boardCount = fake()->numberBetween(2, 4);
      $boardNames = ['To Do', 'In Progress', 'Review', 'Done', 'Backlog'];

      for ($i = 0; $i < $boardCount; $i++) {
        $board = Board::factory()->create([
          'project_id' => $project->uuid,
          'name' => $boardNames[$i] ?? "Board " . ($i + 1),
          'slug' => strtolower(str_replace(' ', '-', $boardNames[$i] ?? "board-" . ($i + 1))),
        ]);
        $boards->push($board);
      }
    });

    // Create tasks
    $this->command->info('✅ Creating tasks...');
    $allUsers = collect([$admin, $projectManager])->merge($teamMembers)->merge($regularUsers);

    $boards->each(function ($board) use ($allUsers) {
      $taskCount = fake()->numberBetween(3, 12);

      Task::factory()
        ->count($taskCount)
        ->create([
          'board_id' => $board->uuid,
          'project_id' => $board->project_id,
          'assigned_to' => $allUsers->random()->id,
          'assigned_by' => $allUsers->random()->id,
        ]);
    });

    // Create some tasks completed this week for reporting
    $this->command->info('📈 Creating weekly completed tasks...');
    Task::factory()
      ->completedThisWeek()
      ->count(15)
      ->create([
        'board_id' => $boards->random()->uuid,
        'project_id' => $projects->random()->uuid,
        'assigned_to' => $allUsers->random()->id,
        'assigned_by' => $allUsers->random()->id,
      ]);

    // Create interactions
    $this->command->info('💬 Creating interactions...');
    $projects->each(function ($project) use ($allUsers) {
      $interactionCount = fake()->numberBetween(2, 8);

      Interaction::factory()
        ->count($interactionCount)
        ->create([
          'contact_id' => $project->contact_id,
          'project_id' => $project->uuid,
          'user_id' => $allUsers->random()->id,
        ]);
    });

    // Create some interactions requiring follow-up
    Interaction::factory()
      ->requiresFollowUp()
      ->count(5)
      ->create([
        'contact_id' => $contacts->random()->uuid,
        'project_id' => $projects->random()->uuid,
        'user_id' => $allUsers->random()->id,
      ]);

    // Create overdue follow-up interactions
    Interaction::factory()
      ->overdueFollowUp()
      ->count(3)
      ->create([
        'contact_id' => $contacts->random()->uuid,
        'project_id' => $projects->random()->uuid,
        'user_id' => $allUsers->random()->id,
      ]);

    // Create reports
    $this->command->info('📊 Creating reports...');

    // Weekly reports
    Report::factory()
      ->weekly()
      ->sent()
      ->count(8)
      ->create(['generated_by' => $admin->id])
      ->each(function ($report) use ($projects) {
        // Add report items for each report
        $projectsForReport = $projects->random(rand(2, 5));

        $projectsForReport->each(function ($project) use ($report) {
          ReportItem::factory()->create([
            'report_id' => $report->uuid,
            'project_id' => $project->uuid,
            'project_name' => $project->name,
            'contact_name' => $project->contact->full_name,
            'firm_name' => $project->contact->firm->name ?? 'Unknown Firm',
          ]);
        });
      });

    // Custom reports
    Report::factory()
      ->custom()
      ->generated()
      ->count(3)
      ->create(['generated_by' => $projectManager->id])
      ->each(function ($report) use ($projects) {
        $projectsForReport = $projects->random(rand(1, 3));

        $projectsForReport->each(function ($project) use ($report) {
          ReportItem::factory()->create([
            'report_id' => $report->uuid,
            'project_id' => $project->uuid,
            'project_name' => $project->name,
            'contact_name' => $project->contact->full_name,
            'firm_name' => $project->contact->firm->name ?? 'Unknown Firm',
          ]);
        });
      });

    // Draft reports
    Report::factory()
      ->draft()
      ->count(2)
      ->create(['generated_by' => $admin->id]);

    $this->command->info('✅ Development data seeded successfully!');
    $this->command->info('');
    $this->command->info('📊 Summary:');
    $this->command->info("👥 Users: " . User::count());
    $this->command->info("🏢 Firms: " . Firm::count());
    $this->command->info("👤 Contacts: " . Contact::count());
    $this->command->info("📋 Projects: " . Project::count());
    $this->command->info("📊 Boards: " . Board::count());
    $this->command->info("✅ Tasks: " . Task::count());
    $this->command->info("💬 Interactions: " . Interaction::count());
    $this->command->info("📈 Reports: " . Report::count());
    $this->command->info('');
    $this->command->info('🔑 Login credentials:');
    $this->command->info('Admin: admin@example.com / password');
    $this->command->info('PM: pm@example.com / password');
  }
}
