<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Firm;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
  /**
   * Run the database seeds.
   */
  public function run(): void
  {
    Contact::factory(10)->create(fn () => [

      'firm_id' => Firm::all()->random()->id

    ]);
  }
}
