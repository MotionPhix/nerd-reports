<?php

use App\Jobs\GenerateWeeklyReportsJob;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new GenerateWeeklyReportsJob)
  ->everyMinute();
  //->weeklyOn(5, '16:30'); // Friday 4:30 PM
