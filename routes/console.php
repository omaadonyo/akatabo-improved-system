<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('backup:database')->daily()->withoutOverlapping();

Schedule::command('invoices:process-recurring')->dailyAt('08:00');
