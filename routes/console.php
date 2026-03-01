<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('installments:check-overdue')->dailyAt('07:00');
