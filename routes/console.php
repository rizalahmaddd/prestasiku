<?php

use App\Jobs\CreateBackup;
use App\Models\BackupSchedule;
use App\Models\SiteEvent;
use App\Models\SiteVisit;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Statistik pengunjung disimpan sebatas SiteVisit::RETENTION_MONTHS.
Schedule::command('model:prune', ['--model' => [SiteVisit::class, SiteEvent::class]])->daily();

// Jadwal diatur superadmin di Pengaturan > Backup & Restore, jadi dicek tiap menit.
Schedule::call(function () {
    BackupSchedule::active()->get()
        ->filter(fn (BackupSchedule $schedule) => $schedule->isDueAt(now()))
        ->each(fn (BackupSchedule $schedule) => CreateBackup::dispatch($schedule->scope, scheduleId: $schedule->id, includeSecrets: $schedule->include_secrets));
})->everyMinute()->name('backup-schedules');
