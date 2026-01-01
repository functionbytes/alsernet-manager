<?php

namespace Modules\Notification\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushNotificationToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token',
        'device_type',
        'device_id',
        'active',
        'last_used_at',
    ];

    protected $casts = [
        'active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    public function deactivate(): void
    {
        $this->update(['active' => false]);
    }

    public static function getActiveTokensForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->where('active', true)
            ->get();
    }
}
