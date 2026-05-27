<?php

namespace App\Events;

use App\Api\V1\Dto\AuditLogDto;
use App\Models\AuditLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly AuditLogDto $dto
    ) {
        // Persist immediately on construction — no listener needed
        AuditLog::create([
            'user_id'      => $dto->userId,
            'action'       => $dto->action,
            'domain'       => $dto->domain,
            'subject_type' => $dto->subjectType,
            'subject_id'   => $dto->subjectId,
            'payload'      => $dto->payload,
            'message'      => $dto->message,
            'ip_address'   => $dto->ipAddress,
            'user_agent'   => $dto->userAgent,
        ]);
    }
}
