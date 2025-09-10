<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_name',
        'device_id',
        'layout_id',
    ];

    protected $casts = [
        'device_id' => 'integer',
        'layout_id' => 'integer',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function layout(): BelongsTo
    {
        return $this->belongsTo(DeviceLayout::class, 'layout_id');
    }


    public function medias(): HasMany
    {
        return $this->hasMany(ScheduleMedia::class);
    }
}
