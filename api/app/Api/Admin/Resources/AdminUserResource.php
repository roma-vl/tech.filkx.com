<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $sub = $this->subscription;
        $limits = $sub ? $sub->getEffectiveLimits() : [
            'concurrent_streams' => 1,
            'storage_limit' => 10,
        ];

        $concurrentStreams = $limits['concurrent_streams'] ?? $limits['concurrentStreams'] ?? 1;
        $storageLimit = $limits['storage_limit'] ?? $limits['storageLimit'] ?? 10;

        $status = $this->status ?? 'active';
        if ($this->trashed()) {
            $status = 'deleted';
        } elseif ($sub && $this->status !== 'suspended') {
            $status = $sub->status->value;
        }

        $storageLimit = (int) $storageLimit;
        // If it looks like bytes (large number), convert to GB. Otherwise assume it's already GB.
        if ($storageLimit > 10000) {
            $storageLimit = (int) ($storageLimit / (1024 * 1024 * 1024));
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'verified' => $this->email_verified_at !== null,
            'plan' => $sub?->plan?->name ?? 'Free',
            'status' => $status,
            'streamsCount' => (int) ($sub?->usage?->streams_active ?? 0),
            'streamsLimit' => (int) $concurrentStreams,
            'storageUsed' => round(($sub?->usage?->storage_used ?? 0) / (1024 * 1024 * 1024), 2), // GB
            'storageLimit' => $storageLimit, // GB
            'createdAt' => $this->created_at->toISOString(),
            'avatarColor' => '#'.substr(md5($this->email), 0, 6),
            'roles' => $this->roles->pluck('slug'),
            'subscription' => $sub ? [
                'id' => $sub->id,
                'featuresSnapshot' => $sub->features_snapshot,
                'effectiveLimits' => $limits,
                'effectiveFeatures' => $sub->getEffectiveLimits(), // This returns the whole merged array
                'addons' => $sub->addons->map(fn($a) => [
                    'id' => $a->id,
                    'name' => $a->addon->name,
                    'quantity' => $a->quantity,
                    'effect' => $a->calculateEffect($a->quantity),
                    'status' => $a->isActive() ? 'active' : 'inactive',
                ]),
                'usage' => $sub->usage ? [
                    'videosUploaded' => (int) ($sub->usage->videos_uploaded ?? 0),
                    'streamsActive' => (int) ($sub->usage->streams_active ?? 0),
                    'storageUsed' => (float) ($sub->usage->storage_used ?? 0),
                    'videoDurationMinutes' => (int) ($sub->usage->video_duration_minutes ?? 0),
                ] : null,
            ] : null,
        ];
    }
}
