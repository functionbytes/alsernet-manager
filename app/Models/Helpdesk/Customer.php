<?php

namespace App\Models\Helpdesk;

use App\Models\Helpdesk\Concerns\HasCustomAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasCustomAttributes, HasFactory, SoftDeletes;

    protected $connection = 'helpdesk';

    protected $table = 'helpdesk_customers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_url',
        'country',
        'state',
        'city',
        'postal_code',
        'language',
        'timezone',
        'custom_attributes',
        'mail_verified_at',
        'banned_at',
        'ban_reason',
        'last_seen_at',
        'total_conversations',
        'total_page_visits',
        'internal_notes',
    ];

    protected $casts = [
        'mail_verified_at' => 'datetime',
        'banned_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'custom_attributes' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $appends = [
        'is_banned',
        'is_verified',
    ];

    /**
     * Get all conversations for this customer
     */
    public function conversations()
    {
        return $this->hasMany(Conversation::class, 'customer_id');
    }

    /**
     * Get all sessions for this customer
     */
    public function sessions()
    {
        return $this->hasMany(CustomerSession::class, 'customer_id');
    }

    /**
     * Get the latest session
     */
    public function latestSession()
    {
        return $this->hasOne(CustomerSession::class, 'customer_id')
            ->latest('created_at');
    }

    /**
     * Get all page visits
     */
    public function pageVisits()
    {
        return $this->hasMany(PageVisit::class, 'customer_id');
    }

    /**
     * Get all emails for this customer
     */
    public function emails()
    {
        return $this->hasMany(CustomerEmail::class, 'customer_id');
    }

    /**
     * Get customer details
     */
    public function details()
    {
        return $this->hasOne(CustomerDetails::class, 'customer_id');
    }

    /**
     * Scope: Get only banned customers
     */
    public function scopeBanned($query)
    {
        return $query->whereNotNull('banned_at');
    }

    /**
     * Scope: Get only verified customers
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('mail_verified_at');
    }

    /**
     * Scope: Get only active (not banned) customers
     */
    public function scopeActive($query)
    {
        return $query->whereNull('banned_at');
    }

    /**
     * Scope: Search by name or email
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('name', 'like', "%{$term}%")
            ->orWhere('email', 'like', "%{$term}%")
            ->orWhere('phone', 'like', "%{$term}%");
    }

    /**
     * Check if customer is banned
     */
    public function getIsBannedAttribute()
    {
        return $this->banned_at !== null;
    }

    /**
     * Check if customer email is verified
     */
    public function getIsVerifiedAttribute()
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Ban a customer
     */
    public function ban($reason = null)
    {
        $this->update([
            'banned_at' => now(),
            'ban_reason' => $reason,
        ]);

        return $this;
    }

    /**
     * Unban a customer
     */
    public function unban()
    {
        $this->update([
            'banned_at' => null,
            'ban_reason' => null,
        ]);

        return $this;
    }

    /**
     * Verify customer email
     */
    public function verifyEmail()
    {
        $this->update([
            'mail_verified_at' => now(),
        ]);

        return $this;
    }

    /**
     * Update last seen activity
     */
    public function updateLastSeen()
    {
        $this->update([
            'last_seen_at' => now(),
        ]);

        return $this;
    }

    /**
     * Increment conversation count
     */
    public function incrementConversationCount()
    {
        $this->increment('total_conversations');
    }

    /**
     * Increment page visit count
     */
    public function incrementPageVisitCount()
    {
        $this->increment('total_page_visits');
    }

    /**
     * Get unread conversations count
     */
    public function getUnreadConversationsCount()
    {
        return $this->conversations()
            ->whereHas('status', fn ($q) => $q->where('category', 'open'))
            ->count();
    }

    /**
     * Get avatar URL or default
     */
    public function getAvatarUrl()
    {
        return $this->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($this->name);
    }
}
