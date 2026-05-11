<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            ['student_id' => 101, 'first_name' => 'Anna', 'last_name' => 'Huber', 'class_name' => '3AHIT'],
            ['student_id' => 102, 'first_name' => 'Lukas', 'last_name' => 'Steiner', 'class_name' => '3AHIT'],
            ['student_id' => 103, 'first_name' => 'Mia', 'last_name' => 'Gruber', 'class_name' => '2AHIT'],
            ['student_id' => 104, 'first_name' => 'Felix', 'last_name' => 'Wagner', 'class_name' => '4AHIT'],
            ['student_id' => 105, 'first_name' => 'Sarah', 'last_name' => 'Leitner', 'class_name' => '1AHMBA'],
            ['student_id' => 106, 'first_name' => 'David', 'last_name' => 'Moser', 'class_name' => '5AHIT'],
        ];

        foreach ($students as $studentData) {
            Student::updateOrCreate(
                ['student_id' => $studentData['student_id']],
                $studentData
            );

            User::updateOrCreate(
                ['id' => $studentData['student_id']],
                [
                    'name' => "{$studentData['first_name']} {$studentData['last_name']}",
                    'email' => strtolower("{$studentData['first_name']}.{$studentData['last_name']}@example.test"),
                    'password' => Hash::make('password'),
                    'klasse' => $studentData['class_name'],
                    'is_teacher' => false,
                    'is_admin' => false,
                    'teacher_id' => null,
                ]
            );
        }
    }
}
