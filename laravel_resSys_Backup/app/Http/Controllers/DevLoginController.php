<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DevLoginController extends Controller
{
    public function showDevLogin()
    {
        // Nur in lokaler Entwicklung erlauben
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        return view('dev-login');
    }

    public function loginAsStudent(Request $request): RedirectResponse
    {
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        $firstname = $request->input('firstname', 'Student');
        $lastname = $request->input('lastname', 'Test');
        $className = $request->input('class', '3AHIT');

        // Speichere temporär die Daten in Session
        $request->session()->put('dev_student_firstname', $firstname);
        $request->session()->put('dev_student_lastname', $lastname);
        $request->session()->put('dev_student_class', $className);

        $user = $this->createOrGetStudentUser($firstname, $lastname, $className);

        Auth::login($user);

        return redirect()->route('student.booking');
    }

    public function loginAsTeacher(Request $request): RedirectResponse
    {
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        $teacherId = $request->input('teacher_id');

        // Speichere temporär die Daten in Session
        $request->session()->put('dev_teacher_id', $teacherId);

        $user = $this->createOrGetTeacherUser($teacherId);

        Auth::login($user);

        return redirect()->route('teacher.dashboard');
    }

    public function loginAsAdmin(): RedirectResponse
    {
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        $user = $this->createOrGetAdminUser();

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    private function isDevEnvironment(): bool
    {
        return app()->environment('local', 'development') || env('APP_DEBUG', false);
    }

    private function createOrGetStudentUser(string $firstname, string $lastname, string $className): User
    {
        // Eindeutige Email basierend auf Name + Klasse für separaten Account
        $emailSlug = Str::slug("{$firstname}-{$lastname}-{$className}");
        $email = "dev-student-{$emailSlug}@test.local";

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(Str::random(32));
        }

        $user->name = "{$firstname} {$lastname}";
        $user->klasse = $className;
        $user->is_teacher = false;
        $user->is_admin = false;
        $user->save();

        // Student-Record erstellen/aktualisieren
        Student::updateOrCreate(
            ['student_id' => $user->id],
            [
                'first_name' => $firstname,
                'last_name' => $lastname,
                'class_name' => $className,
            ]
        );

        return $user;
    }

    private function createOrGetTeacherUser(?string $teacherId = null): User
    {
        $email = "dev-teacher@test.local";

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(Str::random(32));
        }

        $teacher = Teacher::find($teacherId) ?? Teacher::first();
        $user->name = $teacher?->full_name ?? 'Teacher Test';
        $user->klasse = null;
        $user->is_teacher = true;
        $user->is_admin = false;
        $user->teacher_id = $teacher?->teacher_id;

        $user->save();

        return $user;
    }

    private function createOrGetAdminUser(): User
    {
        $email = "dev-admin@test.local";

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(Str::random(32));
        }

        $user->name = 'Admin Test';
        $user->klasse = null;
        $user->is_teacher = false;
        $user->is_admin = true;

        $user->save();

        return $user;
    }
}
