<?php

namespace Database\Seeders;

use App\Models\Email;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmailSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Email::factory(25)->create(fn () => [

      'emailable_id' => \App\Models\Contact::all()->random()->id

    ]);
  }
}
