<?php

namespace App\Api\V1\Actions\Support;

use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\UploadedFile;

class CreateSupportTicketAction
{
    public function __construct(
        protected StoreSupportAttachmentAction $storeSupportAttachmentAction
    ) {}

    public function execute(User $user, array $validated, ?UploadedFile $file): SupportTicket
    {
        $ticket = SupportTicket::create([
            'user_id' => $user->id,
            'product_id' => $validated['product_id'] ?? null,
            'subject' => $validated['subject'],
            'status' => 'new',
            'handled_by' => 'human',
        ]);

        $ticket->messages()->create(array_merge([
            'user_id' => $user->id,
            'message' => $validated['message'],
            'is_admin' => false,
        ], $this->storeSupportAttachmentAction->execute($file)));

        return $ticket->load(['messages', 'product.variants']);
    }
}
