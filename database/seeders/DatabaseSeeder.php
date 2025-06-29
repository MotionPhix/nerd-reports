<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    $this->call([
      RoleAndPermissionSeeder::class,
      ReportTemplateSeeder::class,
    ]);

    // Only seed development data if we're not in production
    if (!app()->environment('production')) {
      $this->call([
        DevelopmentSeeder::class,
      ]);
    }
  }
}
