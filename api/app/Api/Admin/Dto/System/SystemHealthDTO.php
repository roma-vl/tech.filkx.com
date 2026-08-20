<?php

namespace App\Api\Admin\Dto\System;

class SystemHealthDTO
{
    public function __construct(
        public readonly array $server,
        public readonly array $database,
        public readonly array $services,
        public readonly array $network
    ) {}

    public function toArray(): array
    {
        return [
            'server' => $this->server,
            'database' => [
                'status' => $this->database['status'] ?? null,
                'latency' => $this->database['latency'] ?? null,
                'connections' => $this->database['connections'] ?? null,
                'maxConnections' => $this->database['max_connections'] ?? null,
                'error' => $this->database['error'] ?? null,
            ],
            'services' => $this->services,
            'network' => [
                'incoming' => $this->network['incoming'] ?? 0,
                'outgoing' => $this->network['outgoing'] ?? 0,
                'totalReceived' => $this->network['total_received'] ?? 0,
                'totalSent' => $this->network['total_sent'] ?? 0,
                'max' => $this->network['max'] ?? 0,
            ],
        ];
    }
}
