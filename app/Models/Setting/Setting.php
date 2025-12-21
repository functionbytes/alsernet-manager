<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Setting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia , LogsActivity;

    protected $table = 'settings';

    protected static $recordEvents = ['updated'];

    protected $fillable = [
        'key', 'value',
    ];

    public function scopeKey($query, $key)
    {
        return $query->where('key', $key)->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'text']);
    }
}
