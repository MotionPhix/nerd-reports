<?php

namespace Database\Seeders;

use App\Models\Board;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoardSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Board::factory(30)->create(fn () => [

      'project_id' => \App\Models\Project::all()->random()->id

    ]);
  }
}
