<?php

namespace App\Models;

use App\Api\V1\Enum\SupportStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subject',
        'status',
        'handled_by',
        'tags',
    ];

    protected $casts = [
        'status' => SupportStatusEnum::class,
        'tags' => 'array',
    ];

    protected $appends = ['unread_count'];

    public function getUnreadCountAttribute()
    {
        return $this->attributes['unread_count'] ?? 0;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class)->orderBy('created_at', 'asc');
    }

    public function publicMessages(): HasMany
    {
        return $this->messages()->where('is_internal', false);
    }

    public function lastPublicMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
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

    public function lastMessage(): \Illuminate\Database\Eloquent\Relations\HasOne
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
