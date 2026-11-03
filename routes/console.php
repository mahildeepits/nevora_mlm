<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('matrix:sanitize-tree {--dry-run : Only report unpaid positions without deleting}', function () {
    $this->call(\App\Console\Commands\SanitizeMatrixTreeCommand::class, [
        '--dry-run' => $this->option('dry-run')
    ]);
})->purpose('Sanitize 3-matrix tree by purging unpaid users from slot_positions and repairing parent linkages');


