<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $primaryKey = 'teacher_id';

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'integer';

    protected $fillable = [
        'teacher_id',
        'first_name',
        'last_name',
        'kuerzel',
        'classes',
        'timeslot_duration',
        'duration_changed_at',
        'duration_changed_by_teacher',
    ];

    protected $casts = [
        'classes' => 'array',
        'duration_changed_at' => 'datetime',
        'duration_changed_by_teacher' => 'boolean',
    ];

    public function timeslots(): HasMany
    {
        return $this->hasMany(Timeslot::class, 'teacher_id', 'teacher_id');
    }

    public function parentDaySettings(): HasMany
    {
        return $this->hasMany(TeacherParentDaySetting::class, 'teacher_id', 'teacher_id');
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }
}
