<?php

namespace Database\Seeders;

use App\Models\Firm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FirmSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Firm::factory(5)->create();
  }
}
