<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Task::factory(50)->create(fn () => [
      'board_id' => Board::all()->random()->id,
      'assigned_to' => User::where('role', '!=', 'admin')->inRandomOrder()->first()->id,
      'assigned_by' => User::where('role', '!=', 'admin')->inRandomOrder()->first()->id,
    ]);
  }
}
