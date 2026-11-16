<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Real-time expiry is enforced by PlanService; this keeps the stored plan
// column in sync for dashboards and the admin list.
Schedule::command('plans:expire')->dailyAt('00:05');
