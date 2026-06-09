<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExcelImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'filename',
        'file_size',
        'parent_days',
        'teachers_created',
        'teachers_updated',
        'rows_skipped',
        'timeslots_created',
        'timeslots_existing',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'parent_days' => 'array',
        'teachers_created' => 'integer',
        'teachers_updated' => 'integer',
        'rows_skipped' => 'integer',
        'timeslots_created' => 'integer',
        'timeslots_existing' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
