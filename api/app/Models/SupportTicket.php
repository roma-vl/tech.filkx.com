<?php

namespace App\Models;

use App\Api\V1\Enum\SupportStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'subject',
        'status',
        'handled_by',
        'tags',
        'read_at',
    ];

    protected $casts = [
        'status' => SupportStatusEnum::class,
        'tags' => 'array',
        'read_at' => 'datetime',
    ];

    protected $appends = ['unread_count'];

    public function getUnreadCountAttribute()
    {
        // The `unreadCount` alias comes from `withCount([... as unreadCount])` in the
        // list actions; it lands in $attributes verbatim, not snake_cased.
        return $this->attributes['unreadCount'] ?? 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at', 'asc');
    }

    public function publicMessages(): HasMany
    {
        return $this->messages()->where('is_internal', false);
    }

    public function lastPublicMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class)->where('is_internal', false)->latestOfMany();
    }

    public function unreadMessagesForUser(): HasMany
    {
        return $this->messages()->where('is_admin', true)->whereNull('read_at');
    }

    public function unreadMessagesForAdmin(): HasMany
    {
        return $this->messages()->where('is_admin', false)->whereNull('read_at');
    }

    public function lastMessage(): HasOne
    {
        return $this->hasOne(SupportMessage::class)->latestOfMany();
    }

    public function scopeSearch($query, $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('id', 'like', "%{$search}%")
                ->orWhere('subject', 'like', "%{$search}%")
                ->orWhereHas('user', function ($uq) use ($search) {
                    $uq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
        });
    }
}
