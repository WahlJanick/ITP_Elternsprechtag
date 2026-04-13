<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $primaryKey = 'student_id';
    public $incrementing = false;
    protected $keyType = 'integer';
    
    protected $fillable = [
        'student_id',
        'first_name',
        'last_name',
        'class_name',
    ];
    public function timeslots(): HasMany
    {
        return $this->hasMany(Timeslot::class, 'student_id', 'student_id');
    }
    public function getFullNameAttribute(): string 
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
