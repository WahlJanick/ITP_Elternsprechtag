<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
        $existingUser = User::query()->where('email', $email)->first();

        $isAdmin = $this->isAdminEmail($email)
            || (bool) $existingUser?->is_admin
            || Str::contains($normalizedJobTitle, ['admin', 'administrator']);

        $isTeacher = ! $isAdmin && (
            (bool) $existingUser?->is_teacher
            || (bool) $existingUser?->teacher_id
            || Str::contains($normalizedJobTitle, ['teacher', 'lehrer'])
        );

        $user = $existingUser ?? User::firstOrNew(['email' => $email]);

        if (! $user->exists || blank($user->password)) {
            $user->password = Hash::make(Str::random(32));
        }

        $user->fill([
            'name' => $azureUser->getName() ?: $email,
            'klasse' => $jobTitle ?: null,
            'is_teacher' => $isTeacher,
            'is_admin' => $isAdmin,
            'teacher_id' => $user->teacher_id,
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
}
