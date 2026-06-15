<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ParentDay extends Model
{
    protected $fillable = [
        'date',
        'label',
        'is_active_for_students',
    ];

    protected $casts = [
        'date' => 'date',
        'is_active_for_students' => 'boolean',
    ];

    public function timeslots(): HasMany
    {
        return $this->hasMany(Timeslot::class, 'parent_day_id');
    }

    public function teacherSettings(): HasMany
    {
        return $this->hasMany(TeacherParentDaySetting::class, 'parent_day_id');
    }

    public static function ensureDefaultFromLegacy(): ?self
    {
        if (! Schema::hasTable('parent_days')) {
            return null;
        }

        $existing = self::query()->orderBy('date')->first();

        if ($existing) {
            return $existing;
        }

        $legacyDate = null;

        if (Schema::hasTable('settings')) {
            $stored = DB::table('settings')
                ->where('key', 'parent_day')
                ->value('value');

            if ($stored) {
                $legacyDate = Carbon::parse($stored)->toDateString();
            }
        }

        if (! $legacyDate && Schema::hasTable('timeslots')) {
            $fallback = DB::table('timeslots')
                ->orderBy('starts_at')
                ->value('starts_at');

            if ($fallback) {
                $legacyDate = Carbon::parse($fallback)->toDateString();
            }
        }

        if (! $legacyDate) {
            return null;
        }

        return self::query()->create([
            'date' => $legacyDate,
        ]);
    }
}
