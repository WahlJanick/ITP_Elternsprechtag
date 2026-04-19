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

    public function loginAsStudent(): RedirectResponse
    {
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        $user = $this->createOrGetUser('student');

        Auth::login($user);

        return redirect()->route('student.booking');
    }

    public function loginAsTeacher(): RedirectResponse
    {
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        $user = $this->createOrGetUser('teacher');

        Auth::login($user);

        return redirect()->route('teacher.dashboard');
    }

    public function loginAsAdmin(): RedirectResponse
    {
        if (! $this->isDevEnvironment()) {
            abort(404);
        }

        $user = $this->createOrGetUser('admin');

        Auth::login($user);

        return redirect()->route('admin.dashboard');
    }

    private function isDevEnvironment(): bool
    {
        return app()->environment('local', 'development') || env('APP_DEBUG', false);
    }

    private function createOrGetUser(string $role): User
    {
        $email = "dev-{$role}@test.local";

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(Str::random(32));
        }

        $userData = [
            'name' => ucfirst($role) . ' Test',
            'klasse' => $role === 'student' ? '3AHIT' : null,
            'is_teacher' => $role === 'teacher',
            'is_admin' => $role === 'admin',
        ];

        // Für Lehrer: Verknüpfung mit existierendem Teacher
        if ($role === 'teacher') {
            $teacher = Teacher::first();
            if ($teacher) {
                $userData['teacher_id'] = $teacher->teacher_id;
            }
        }

        $user->fill($userData);
        $user->save();

        // Für Student: Auch Student-Record erstellen
        if ($role === 'student') {
            Student::updateOrCreate(
                ['student_id' => $user->id],
                [
                    'first_name' => 'Student',
                    'last_name' => 'Test',
                    'class_name' => '3AHIT',
                ]
            );
        }

        return $user;
    }
}
