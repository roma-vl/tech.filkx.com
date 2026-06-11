<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Schema(
 *     schema="AuditLog",
 *     type="object",
 *
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="action", type="string"),
 *     @OA\Property(property="domain", type="string"),
 *     @OA\Property(property="message", type="string"),
 *     @OA\Property(property="user", type="object",
 *         @OA\Property(property="id", type="integer"),
 *         @OA\Property(property="name", type="string"),
 *         @OA\Property(property="email", type="string"),
 *         @OA\Property(property="avatar", type="string", nullable=true)
 *     ),
 *     @OA\Property(property="subjectType", type="string", nullable=true),
 *     @OA\Property(property="subjectId", type="integer", nullable=true),
 *     @OA\Property(property="payload", type="object", nullable=true),
 *     @OA\Property(property="ipAddress", type="string"),
 *     @OA\Property(property="userAgent", type="string"),
 *     @OA\Property(property="createdAt", type="string", format="date-time")
 * )
 */
class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'domain' => $this->domain,
            'message' => $this->message,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
                'avatar' => $this->user?->avatar_path
                    ? Storage::disk('public')->url($this->user?->avatar_path)
                    : null,
            ],
            'subjectType' => $this->subject_type,
            'subjectId' => $this->subject_id,
            'payload' => $this->payload,
            'ipAddress' => $this->ip_address,
            'userAgent' => $this->user_agent,
            'createdAt' => $this->created_at->toIso8601String(),
        ];
    }
}
