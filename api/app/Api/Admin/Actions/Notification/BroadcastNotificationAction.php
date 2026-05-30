<?php

namespace App\Api\Admin\Actions\Notification;

use App\Api\V1\Repositories\NotificationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class BroadcastNotificationAction
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function execute(array $data): void
    {
        DB::transaction(function () use ($data) {
            $title = $data['title'];
            $content = $data['message'];
            $type = $data['type'];
            $link = $data['action_url'] ?? null;

            if ($data['recipients'] === 'all') {
                // Create a single global notification
                $this->notificationRepository->create([
                    'user_id' => null,
                    'title' => $title,
                    'content' => $content,
                    'type' => $type,
                    'link' => $link,
                ]);
            } else {
                // Create separate notifications for selected users
                foreach ($data['user_ids'] as $userId) {
                    $this->notificationRepository->create([
                        'user_id' => $userId,
                        'title' => $title,
                        'content' => $content,
                        'type' => $type,
                        'link' => $link,
                    ]);
                }
            }
        });
    }
}
