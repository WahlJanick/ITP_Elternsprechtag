<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timeslot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'teacher_id',
        'parent_day_id',
        'student_id',
        'starts_at',
        'ends_at',
        'room',
        'is_reserved',
        'day',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_reserved' => 'boolean',
        'day' => 'date',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'teacher_id', 'teacher_id');
    }

    public function parentDay(): BelongsTo
    {
        return $this->belongsTo(ParentDay::class, 'parent_day_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'student_id');
    }
}
