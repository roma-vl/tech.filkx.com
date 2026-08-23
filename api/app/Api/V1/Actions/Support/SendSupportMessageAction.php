<?php

namespace App\Api\V1\Actions\Support;

use App\Api\V1\Enum\SupportStatusEnum;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class SendSupportMessageAction
{
    public function __construct(
        protected StoreSupportAttachmentAction $storeSupportAttachmentAction
    ) {}

    public function execute(SupportTicket $ticket, User $user, array $validated, ?UploadedFile $file): SupportMessage
    {
        if ($ticket->user_id !== $user->id) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if (! ($validated['message'] ?? null) && ! $file) {
            throw new UnprocessableEntityHttpException('Message or file is required');
        }

        $message = $ticket->messages()->create(array_merge([
            'user_id' => $user->id,
            'message' => $validated['message'] ?? null,
            'is_admin' => false,
        ], $this->storeSupportAttachmentAction->execute($file)));

        if ($ticket->status === SupportStatusEnum::DONE) {
            $ticket->update(['status' => SupportStatusEnum::ACCEPTED]);
        }

        return $message;
    }
}
