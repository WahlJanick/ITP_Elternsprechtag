<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TimeslotController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'anmelden')->name('login');

// Dev Login (nur für lokale Entwicklung)
Route::get('/dev-login', [DevLoginController::class, 'showDevLogin'])->name('dev.login');
Route::match(['get', 'post'], '/dev-login/student', [DevLoginController::class, 'loginAsStudent'])->name('dev.login.student');
Route::match(['get', 'post'], '/dev-login/teacher', [DevLoginController::class, 'loginAsTeacher'])->name('dev.login.teacher');
Route::get('/dev-login/admin', [DevLoginController::class, 'loginAsAdmin'])->name('dev.login.admin');
Route::view('/home', 'home');

Route::get('/register', function () {
    return redirect()->route('login');
});

Route::get('/books', function () {
    return redirect()->route('login');
});

Route::get('/teacher', function () {
    return redirect()->route('teacher.dashboard');
});

Route::get('/booked', function () {
    return redirect()->route('student.bookings');
});

Route::resource('students', StudentController::class);
Route::resource('teachers', TeacherController::class);
Route::resource('timeslots', TimeslotController::class);

Route::get('/auth/azure', [AuthController::class, 'redirectToAzure'])->name('auth.azure');
Route::get('/auth/azure/callback', [AuthController::class, 'handleAzureCallback'])->name('auth.azure.callback');

Route::middleware('auth')->group(function () {
    Route::get('/student/booking', [PortalController::class, 'studentDashboard'])->name('student.booking');
    Route::get('/student/teachers', [PortalController::class, 'studentTeachers'])->name('student.teachers.index');
    Route::get('/student/teachers/{teacher}', [PortalController::class, 'studentTeacherShow'])->name('student.teachers.show');
    Route::post('/student/timeslots/{timeslot}/book', [PortalController::class, 'bookTeacherSlot'])->name('student.timeslots.book.direct');
    Route::post('/student/timeslots/book', [PortalController::class, 'bookSlotFromForm'])->name('student.timeslots.book');
    Route::get('/student/bookings', [PortalController::class, 'studentBookings'])->name('student.bookings');
    Route::post('/student/bookings/{timeslot}/cancel', [PortalController::class, 'cancelStudentBooking'])->name('student.bookings.cancel');

    Route::get('/teacher/dashboard', [PortalController::class, 'teacherDashboard'])->name('teacher.dashboard');

    Route::get('/admin', [PortalController::class, 'adminDashboard'])->name('admin.dashboard');
    Route::post('/admin/webuntis/sync', [PortalController::class, 'adminSyncWebUntis'])->name('admin.webuntis.sync');
    Route::get('/admin/teachers/{teacher}', [PortalController::class, 'adminTeacherShow'])->name('admin.teachers.show');
    Route::get('/admin/timeslots/create', [PortalController::class, 'adminTimeslotCreate'])->name('admin.timeslots.create');
    Route::post('/admin/timeslots', [PortalController::class, 'adminTimeslotStore'])->name('admin.timeslots.store');
    Route::get('/admin/timeslots/{timeslot}/edit', [PortalController::class, 'adminTimeslotEdit'])->name('admin.timeslots.edit');
    Route::post('/admin/timeslots/{timeslot}', [PortalController::class, 'adminTimeslotUpdate'])->name('admin.timeslots.update');
    Route::delete('/admin/timeslots/{timeslot}', [PortalController::class, 'adminTimeslotDestroy'])->name('admin.timeslots.destroy');
    Route::post('/admin/timeslots/{timeslot}/release', [PortalController::class, 'adminReleaseSlot'])->name('admin.timeslots.release');

    Route::match(['get', 'post'], '/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});

Route::get('/health', function () {
    $status = [];

    try {
        DB::connection()->getPdo();
        DB::select('SELECT 1');
        $status['database'] = 'OK';
    } catch (\Exception $exception) {
        $status['database'] = 'Error';
    }

    try {
        Cache::store('redis')->put('health_check', 'OK', 10);
        $value = Cache::store('redis')->get('health_check');
        $status['redis'] = $value === 'OK' ? 'OK' : 'Error';
    } catch (\Exception $exception) {
        $status['redis'] = 'Error';
    }

    try {
        $testFile = 'health_check.txt';
        Storage::put($testFile, 'OK');
        $content = Storage::get($testFile);
        Storage::delete($testFile);

        $status['storage'] = $content === 'OK' ? 'OK' : 'Error';
    } catch (\Exception $exception) {
        $status['storage'] = 'Error';
    }

    $isHealthy = collect($status)->every(function ($value) {
        return $value === 'OK';
    });

    return response()->json($status, $isHealthy ? 200 : 503);
});
