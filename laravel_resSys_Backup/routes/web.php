<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'anmelden')->name('login');

// Dev Login (nur für lokale Entwicklung)
if (app()->environment('local')) {
    Route::get('/dev-login', [DevLoginController::class, 'showDevLogin'])->name('dev.login');
    Route::match(['get', 'post'], '/dev-login/student', [DevLoginController::class, 'loginAsStudent'])->name('dev.login.student');
    Route::match(['get', 'post'], '/dev-login/teacher', [DevLoginController::class, 'loginAsTeacher'])->name('dev.login.teacher');
    Route::get('/dev-login/admin', [DevLoginController::class, 'loginAsAdmin'])->name('dev.login.admin');
}

Route::redirect('/register', '/');
Route::redirect('/books', '/');
Route::redirect('/teacher', '/teacher/dashboard');
Route::redirect('/booked', '/student/bookings');

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
    Route::get('/admin/teachers/{teacher}', [PortalController::class, 'adminTeacherShow'])->name('admin.teachers.show');
    Route::get('/admin/timeslots/create', [PortalController::class, 'adminTimeslotCreate'])->name('admin.timeslots.create');
    Route::post('/admin/timeslots', [PortalController::class, 'adminTimeslotStore'])->name('admin.timeslots.store');
    Route::get('/admin/timeslots/{timeslot}/edit', [PortalController::class, 'adminTimeslotEdit'])->name('admin.timeslots.edit');
    Route::post('/admin/timeslots/{timeslot}', [PortalController::class, 'adminTimeslotUpdate'])->name('admin.timeslots.update');
    Route::delete('/admin/timeslots/{timeslot}', [PortalController::class, 'adminTimeslotDestroy'])->name('admin.timeslots.destroy');
    Route::post('/admin/timeslots/{timeslot}/release', [PortalController::class, 'adminReleaseSlot'])->name('admin.timeslots.release');

    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::get('/health', HealthController::class);
