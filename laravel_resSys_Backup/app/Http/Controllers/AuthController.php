<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str as SupportStr;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class AuthController extends Controller
{
    public function redirectToAzure()
    {
        if (Auth::check()) {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return Socialite::driver('azure')
            ->with(['prompt' => 'login'])
            ->redirect();
    }

    public function handleAzureCallback()
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->with('error', 'Microsoft login failed. Please start the sign-in process again.');
        }

        $email = $azureUser->getEmail()
            ?? $azureUser->user['mail']
            ?? $azureUser->user['userPrincipalName']
            ?? null;

        if (! $email) {
            return redirect()
                ->route('login')
                ->with('error', 'Microsoft login did not return an email address for this account.');
        }

        $jobTitle = (string) ($azureUser->user['jobTitle'] ?? '');
        $normalizedJobTitle = Str::lower($jobTitle);
        $teacher = $this->resolveTeacherFromAzureName($azureUser->getName() ?: '');

        $isAdmin = $this->isAdminEmail($email)
            || Str::contains($normalizedJobTitle, ['admin', 'administrator']);

        $isTeacher = ! $isAdmin && (
            $teacher !== null
            || Str::contains($normalizedJobTitle, ['teacher', 'lehrer'])
        );

        $user = User::firstOrNew(['email' => $email]);

        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(Str::random(32));
        }

        $user->fill([
            'name' => $azureUser->getName() ?: $email,
            'klasse' => $jobTitle ?: null,
            'is_teacher' => $isTeacher,
            'is_admin' => $isAdmin,
            'teacher_id' => $teacher?->teacher_id,
        ]);

        $user->save();

        Auth::login($user);
        session(['klasse' => $jobTitle]);

        $targetRoute = $isAdmin
            ? 'admin.dashboard'
            : ($isTeacher ? 'teacher.dashboard' : 'student.booking');

        return redirect()->intended(route($targetRoute));
    }

    private function isAdminEmail(string $email): bool
    {
        $adminEmails = collect(explode(',', (string) env('ADMIN_EMAILS', '')))
            ->map(fn (string $value) => Str::lower(trim($value)))
            ->filter();

        return $adminEmails->contains(Str::lower($email));
    }

    private function resolveTeacherFromAzureName(string $name): ?Teacher
    {
        $normalizedName = $this->normalizeComparableValue($name);

        if ($normalizedName === '') {
            return null;
        }

        return Teacher::query()
            ->get()
            ->first(function (Teacher $teacher) use ($normalizedName) {
                $fullName = $this->normalizeComparableValue($teacher->full_name);
                $reverseName = $this->normalizeComparableValue("{$teacher->last_name} {$teacher->first_name}");

                return $normalizedName === $fullName || $normalizedName === $reverseName;
            });
    }

    private function normalizeComparableValue(string $value): string
    {
        return SupportStr::of($value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();
    }
}
