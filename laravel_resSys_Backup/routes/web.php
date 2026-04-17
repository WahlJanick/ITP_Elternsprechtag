<?php


use App\Http\Controllers\CustombookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AuthController;
use App\Models\User;

Route::get('/student/booking', function () {
    // Wenn die DB leer ist, gibt das eine leere Collection zurück (kein Fehler)
    $teachers = User::where('is_teacher', true)->get();

    return view('student.booking', [
        'teachers' => $teachers
    ]);
})->middleware('auth');

Route::get('/auth/azure', [AuthController::class, 'redirectToAzure']);
Route::get('/auth/azure/callback', [AuthController::class, 'handleAzureCallback']);

Route::get('/', function () {
    Log::info('Page visited');
    return view('anmelden');
});

Route::get('/student/booking', function () {
    // Wir laden nur User, die Lehrer sind, und sortieren sie nach Alphabet
    $teachers = \App\Models\User::where('is_teacher', true)
                ->orderBy('name')
                ->get();

    return view('student.booking', compact('teachers'));
})->middleware('auth');

Route::get('/teacher/dashboard', function () {
    return view('teacher.dashboard'); // Erstelle diese Blade-Datei
})->middleware('auth');

Route::get('/logout', function() {
    Auth::logout();
    return redirect('/');
});


Route::get('/health', function () {
    $status = [];

    // Check Database Connection
    try {
        DB::connection()->getPdo();
        // Optionally, run a simple query
        DB::select('SELECT 1');
        $status['database'] = 'OK';
    } catch (\Exception $e) {
        $status['database'] = 'Error';
    }

    // Check Redis Connection
    try {
        Cache::store('redis')->put('health_check', 'OK', 10);
        $value = Cache::store('redis')->get('health_check');
        if ($value === 'OK') {
            $status['redis'] = 'OK';
        } else {
            $status['redis'] = 'Error';
        }
    } catch (\Exception $e) {
        $status['redis'] = 'Error';
    }

    // Check Storage Access
    try {
        $testFile = 'health_check.txt';
        Storage::put($testFile, 'OK');
        $content = Storage::get($testFile);
        Storage::delete($testFile);

        if ($content === 'OK') {
            $status['storage'] = 'OK';
        } else {
            $status['storage'] = 'Error';
        }
    } catch (\Exception $e) {
        $status['storage'] = 'Error';
    }

    // Determine overall health status
    $isHealthy = collect($status)->every(function ($value) {
        return $value === 'OK';
    });

    $httpStatus = $isHealthy ? 200 : 503;

    return response()->json($status, $httpStatus);
});
