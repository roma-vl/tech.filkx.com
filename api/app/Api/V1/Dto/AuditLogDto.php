<?php

namespace App\Api\V1\Dto;

class AuditLogDto
{
    public function __construct(
        public readonly string  $action,
        public readonly string  $domain,
        public readonly string  $message,
        public readonly ?array  $payload = null,
        public readonly ?int    $userId = null,
        public readonly ?string $subjectType = null,
        public readonly ?string $subjectId = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
    ) {}
}
