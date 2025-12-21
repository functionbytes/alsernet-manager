<?php

namespace App\Models\Setting;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Hour extends Model
{
    use HasFactory , LogsActivity;

    protected $table = 'hours';

    protected static $recordEvents = ['updated'];

    protected $fillable = [
        'weeks',
        'starttime',
        'endtime',
        'status',
        'no_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'text']);
    }
}
