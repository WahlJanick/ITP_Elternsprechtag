<?php

use App\Services\WebUntisTeacherSyncService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('webuntis:sync-teachers', function (WebUntisTeacherSyncService $syncService) {
    $result = $syncService->sync();

    $this->info("WebUntis-Sync erfolgreich. Lehrer: {$result['teachers']}, Klassenzuordnungen: {$result['class_assignments']}");
})->purpose('Synchronisiert Lehrer und Klassen aus WebUntis.');
