<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleMedia extends Model
{
    use HasFactory;

    protected $table = 'schedule_medias';

    protected $fillable = [
        'media_file',
        'schedule_id',
        'media_type',
        'title',
        'duration_seconds',
        'screen_id',
        'schedule_start_date_time',
        'schedule_end_date_time',
        'play_forever',
    ];

    protected $casts = [
        'schedule_id' => 'integer',
        'screen_id' => 'integer',
        'duration_seconds' => 'integer',
        'schedule_start_date_time' => 'datetime',
        'schedule_end_date_time' => 'datetime',
        'play_forever' => 'boolean',
    ];

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    public function screen(): BelongsTo
    {
        return $this->belongsTo(DeviceScreen::class, 'screen_id');
    }
}
