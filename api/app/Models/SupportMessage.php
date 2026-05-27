<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SupportMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'support_ticket_id',
        'user_id',
        'message',
        'file_path',
        'file_type',
        'file_name',
        'file_size',
        'is_admin',
        'is_ai',
        'is_internal',
        'read_at',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_admin' => 'boolean',
        'is_ai' => 'boolean',
        'read_at' => 'datetime',
    ];

    protected $appends = ['file_url'];

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['ticket'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'support_ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        if (! $this->file_path) {
            return null;
        }

        return Storage::disk('public')->url($this->file_path);
    }
}
