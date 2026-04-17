<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function redirectToAzure()
    {
        return Socialite::driver('azure')->redirect();
    }

    public function handleAzureCallback()
{
    // DIESE ZEILE FEHLT BEI DIR:
    $azureUser = \Laravel\Socialite\Facades\Socialite::driver('azure')->user();

    // Erst danach kannst du auf $azureUser zugreifen:
    $jobTitle = $azureUser->user['jobTitle'] ?? '';
    
    $isTeacher = str_contains(strtolower($jobTitle), 'teacher') || str_contains(strtolower($jobTitle), 'lehrer');

    $user = \App\Models\User::updateOrCreate([
        'email' => $azureUser->getEmail(),
    ], [
        'name' => $azureUser->getName(),
        'klasse' => $jobTitle,
        'is_teacher' => $isTeacher,
        // Passwort generieren, um den SQL-Fehler zu vermeiden
        'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(32)),
    ]);

    // ... nachdem der User eingeloggt wurde
\Illuminate\Support\Facades\Auth::login($user);

// DIESE ZEILE HINZUFÜGEN:
session(['klasse' => $jobTitle]); 

    // WICHTIG: Prüfe ob die Pfade mit deinen Routen übereinstimmen
    if ($isTeacher) {
        return redirect('/teacher/dashboard');
    }

    return redirect('/student/booking');
}
}