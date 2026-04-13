<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    use HasFactory;
    protected $primaryKey = 'teacher_id';
    public $incrementing = false;
    protected $keyType = 'integer';
    protected $fillable = [
        'teacher_id',
        'first_name',
        'last_name',
        'classes',
    ];
    protected $casts = [
        'classes' => 'array',
    ];
    public function timeslots(): HasMany
    {
        return $this->hasMany(Timeslot::class, 'teacher_id', 'teacher_id');
    }
}
