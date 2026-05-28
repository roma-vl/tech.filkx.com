<?php

namespace App\Api\Admin\Actions\Support;

use App\Api\V1\Enum\SupportStatusEnum;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;

class ReplySupportTicketAction
{
    /**
     * Reply to a support ticket.
     */
    public function execute(SupportTicket $ticket, array $data, ?UploadedFile $file = null): SupportMessage
    {
        $fileData = [];
        if ($file) {
            $path = $file->store('support/attachments', 'public');
            $fileData = [
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ];
        }

        $isInternal = (bool) ($data['is_internal'] ?? false);

        $message = $ticket->messages()->create(array_merge([
            'user_id' => Auth::id(),
            'message' => $data['message'] ?? null,
            'is_admin' => true,
            'is_internal' => $isInternal,
        ], $fileData));

        if (! $isInternal) {
            if ($ticket->status === SupportStatusEnum::NEW || $ticket->handled_by === 'ai') {
                $ticket->update([
                    'status' => SupportStatusEnum::ACCEPTED,
                    'handled_by' => 'human',
                ]);
            }
        }

        return $message->load('user');
    }
}
