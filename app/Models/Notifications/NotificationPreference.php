<?php

namespace App\Models\Notifications;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'channel',
        'notification_type',
        'is_enabled',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Usuario propietario de la preferencia
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si un canal está habilitado para un tipo de notificación
     */
    public static function isEnabled(int $userId, string $channel, string $type): bool
    {
        $preference = static::where('user_id', $userId)
            ->where('channel', $channel)
            ->where('notification_type', $type)
            ->first();

        // Si no existe preferencia, por defecto está habilitado
        return $preference ? $preference->is_enabled : true;
    }

    /**
     * Habilitar o deshabilitar un tipo de notificación
     */
    public static function toggle(int $userId, string $channel, string $type, bool $enabled): void
    {
        static::updateOrCreate(
            [
                'user_id' => $userId,
                'channel' => $channel,
                'notification_type' => $type,
            ],
            ['is_enabled' => $enabled]
        );
    }

    /**
     * Obtener todas las preferencias de un usuario
     */
    public static function forUser(int $userId): array
    {
        return static::where('user_id', $userId)->get()->groupBy('channel')->toArray();
    }

    /**
     * Scopes
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }
}
