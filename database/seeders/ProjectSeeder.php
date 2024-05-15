<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Project::factory(20)->create(fn () => [
      'contact_id' => Contact::all()->random()->id,
      'created_by' => User::all()->random()->id,
    ]);
  }
}
