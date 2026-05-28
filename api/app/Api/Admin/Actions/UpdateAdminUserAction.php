<?php

namespace App\Api\Admin\Actions;

use App\Api\V1\Dto\AuditLogDto;
use App\Events\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UpdateAdminUserAction
{
    public function execute(int $userId, array $data, string $ip, string $userAgent): User
    {
        $user = User::withTrashed()->findOrFail($userId);

        $userData = array_intersect_key($data, array_flip(['name', 'email', 'status']));
        $user->update($userData);

        if (isset($data['featuresSnapshot']) && $user->subscription) {
            $snapshot = $data['featuresSnapshot'];

            // Normalize top-level keys
            $normalized = [
                'concurrent_streams' => $snapshot['concurrentStreams'] ?? $snapshot['concurrent_streams'] ?? 1,
                'stream_quality' => $snapshot['streamQuality'] ?? $snapshot['stream_quality'] ?? 'hd',
                'platforms' => $snapshot['platforms'] ?? [],
                'storage_limit' => $snapshot['storageLimit'] ?? $snapshot['storage_limit'] ?? 10,
                'watermark_type' => $snapshot['watermarkType'] ?? $snapshot['watermark_type'] ?? 'none',
                'max_videos' => $snapshot['maxVideos'] ?? $snapshot['max_videos'] ?? 0,
                'features' => $snapshot['features'] ?? [],
                'limits' => $snapshot['limits'] ?? [],
            ];

            $user->subscription->update([
                'features_snapshot' => $normalized,
            ]);
        }

        if (isset($data['subscriptionUsage']) && $user->subscription) {
            $usageData = [
                'streams_active' => $data['subscriptionUsage']['streamsActive'] ?? 0,
                'storage_used' => $data['subscriptionUsage']['storageUsed'] ?? 0,
                'videos_uploaded' => $data['subscriptionUsage']['videosUploaded'] ?? 0,
                'video_duration_minutes' => $data['subscriptionUsage']['videoDurationMinutes'] ?? 0,
            ];

            $user->subscription->usage()->updateOrCreate(
                ['subscription_id' => $user->subscription->id],
                $usageData
            );
        }

        event(new AuditEvent(new AuditLogDto(
            action: 'user.updated',
            domain: 'team',
            message: "Admin updated user: {$user->name}",
            payload: $data,
            userId: Auth::id(),
            subjectType: User::class,
            subjectId: $user->id,
            ipAddress: $ip,
            userAgent: $userAgent
        )));

        return $user->fresh(['roles']);
    }
}
