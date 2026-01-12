<?php

namespace Modules\Helpdesk\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Trait HasHelpdeskRelations
 *
 * Gestiona las relaciones del usuario con el sistema de helpdesk:
 * tickets, conversaciones, grupos de agentes, y configuraciones.
 */
trait HasHelpdeskRelations
{
    /**
     * Get user's tickets
     */
    public function tickets(): HasMany
    {
        return $this->hasMany('App\Models\Ticket\Ticket');
    }

    /**
     * Get the agent settings for this user
     */
    public function agentSettings(): HasOne
    {
        return $this->hasOne(\App\Models\Helpdesk\AgentSettings::class);
    }

    /**
     * Get the groups that the user belongs to
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(\App\Models\Helpdesk\Group::class, 'helpdesk_group_user')
            ->withPivot('conversation_priority')
            ->withTimestamps();
    }

    /**
     * Get the conversations assigned to this user
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(\App\Models\Helpdesk\Conversation::class, 'assigned_to');
    }

    /**
     * Check if the user accepts conversations right now
     */
    public function acceptsConversations(): bool
    {
        return $this->agentSettings?->acceptsConversationsNow() ?? false;
    }
}
