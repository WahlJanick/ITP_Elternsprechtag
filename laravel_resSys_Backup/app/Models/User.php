<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'klasse',
        'is_teacher',
        'is_admin',
        'teacher_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_teacher' => 'boolean',
        'is_admin' => 'boolean',
        'password' => 'hashed',
    ];

    public function isAdminUser(): bool
    {
        return (bool) $this->is_admin;
    }

    public function isTeacherUser(): bool
    {
        return (bool) $this->is_teacher;
    }

    public function isStudentUser(): bool
    {
        return ! $this->is_teacher && ! $this->is_admin;
    }
}
