<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherParentDaySetting extends Model
{
    protected $fillable = [
        'teacher_id',
        'parent_day_id',
        'timeslot_duration',
        'room',
        'duration_changed_at',
        'duration_changed_by_teacher',
    ];

    protected $casts = [
        'duration_changed_at' => 'datetime',
        'duration_changed_by_teacher' => 'boolean',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function parentDay(): BelongsTo
    {
        return $this->belongsTo(ParentDay::class, 'parent_day_id');
    }
}
