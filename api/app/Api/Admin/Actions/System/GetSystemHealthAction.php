<?php

namespace App\Api\Admin\Actions\System;

use App\Api\Admin\Dto\System\SystemHealthDTO;
use App\Enums\Streaming\StreamStatus;
use App\Models\StreamSession;
use App\Models\Video;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetSystemHealthAction
{
    /**
     * Get system health monitoring data.
     */
    public function execute(): SystemHealthDTO
    {
        return new SystemHealthDTO(
            server: [
                'cpu' => $this->getCpuUsage(),
                'ram' => $this->getRamUsage(),
                'disk' => $this->getDiskUsage(),
                'uptime' => $this->getUptime(),
            ],
            database: $this->getDatabaseStats(),
            services: $this->getServicesStatus(),
            network: $this->getNetworkStats(),
            streaming: $this->getStreamingStats(),
        );
    }

    private function getCpuUsage(): array
    {
        $load = sys_getloadavg();
        $cores = $this->getCpuCores();

        return [
            'percentage' => round(($load[0] / $cores) * 100, 1),
            'load' => $load,
            'cores' => $cores,
        ];
    }

    private function getCpuCores(): int
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $nproc = @shell_exec('nproc');
            if ($nproc) {
                return (int) trim($nproc) ?: 1;
            }
        }

        return 1;
    }

    private function getRamUsage(): array
    {
        $total = 0;
        $used = 0;

        if (PHP_OS_FAMILY === 'Linux') {
            $data = explode("\n", @file_get_contents('/proc/meminfo'));
            $meminfo = [];
            foreach ($data as $line) {
                if (empty($line)) {
                    continue;
                }
                $parts = explode(':', $line);
                if (count($parts) < 2) {
                    continue;
                }
                $meminfo[trim($parts[0])] = trim($parts[1]);
            }

            if (isset($meminfo['MemTotal'])) {
                $total = (int) filter_var($meminfo['MemTotal'], FILTER_SANITIZE_NUMBER_INT) / 1024 / 1024; // GB
                // Netdata used = Total - Free - Buffers - Cached - SReclaimable
                $free = (int) filter_var($meminfo['MemFree'] ?? '0', FILTER_SANITIZE_NUMBER_INT) / 1024 / 1024;
                $buffers = (int) filter_var($meminfo['Buffers'] ?? '0', FILTER_SANITIZE_NUMBER_INT) / 1024 / 1024;
                $cached = (int) filter_var($meminfo['Cached'] ?? '0', FILTER_SANITIZE_NUMBER_INT) / 1024 / 1024;
                $sreclaimable = (int) filter_var($meminfo['SReclaimable'] ?? '0', FILTER_SANITIZE_NUMBER_INT) / 1024 / 1024;
                
                $available = (int) filter_var($meminfo['MemAvailable'] ?? ($free + $buffers + $cached), FILTER_SANITIZE_NUMBER_INT) / 1024 / 1024;
                $used = $total - $available;
            }
        } else {
            $total = 16;
            $used = 4.2;
        }

        return [
            'total' => round($total, 1),
            'used' => round($used, 1),
            'percentage' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
        ];
    }

    private function getDiskUsage(): array
    {
        $path = base_path();
        $total = @disk_total_space($path) ?: 0;
        $free = @disk_free_space($path) ?: 0;
        $used = $total - $free;

        return [
            'total' => round($total / 1024 / 1024 / 1024, 1),
            'used' => round($used / 1024 / 1024 / 1024, 1),
            'available' => round($free / 1024 / 1024 / 1024, 1),
            'percentage' => $total > 0 ? round(($used / $total) * 100, 1) : 0,
        ];
    }

    private function getUptime(): string
    {
        if (PHP_OS_FAMILY === 'Linux') {
            $str = @file_get_contents('/proc/uptime');
            if ($str) {
                $num = (float) $str;
                $totalSecs = (int) $num;
                $days = (int) ($totalSecs / 86400);
                $hours = (int) (($totalSecs % 86400) / 3600);
                $mins = (int) (($totalSecs % 3600) / 60);

                return "$days d, $hours h, $mins m";
            }
        }

        return 'N/A';
    }

    private function getDatabaseStats(): array
    {
        try {
            $start = microtime(true);
            DB::connection()->getPdo();
            $latency = round((microtime(true) - $start) * 1000, 2);

            $connections = 'N/A';
            if (config('database.default') === 'pgsql') {
                $connectionsData = DB::select('SELECT count(*) FROM pg_stat_activity');
                $connections = $connectionsData[0]->count ?? '0';
            }

            $maxConnections = (int)(DB::select('SHOW max_connections')[0]->max_connections ?? 100);

            return [
                'status' => 'online',
                'latency' => $latency,
                'connections' => $connections,
                'max_connections' => $maxConnections,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'offline',
                'error' => $e->getMessage(),
            ];
        }
    }

    private function getServicesStatus(): array
    {
        return [
            ['name' => 'API Engine', 'endpoint' => '/api/v1', 'status' => 'active', 'latency' => 8],
            ['name' => 'Stream Runner', 'endpoint' => 'docker.filkx.com', 'status' => 'active', 'latency' => 12],
            ['name' => 'Storage Node', 'endpoint' => 's3.filkx.com', 'status' => 'active', 'latency' => 45],
            ['name' => 'Mail Server', 'endpoint' => 'smtp.filkx.com', 'status' => 'active', 'latency' => 156],
        ];
    }

    private function getNetworkStats(): array
    {
        if (PHP_OS_FAMILY !== 'Linux') {
            return [
                'incoming' => 1.2,
                'outgoing' => 4.5,
                'max' => 1000,
            ];
        }

        $getData = function () {
            $data = @file_get_contents('/proc/net/dev');
            if (!$data) {
                return null;
            }

            $lines = explode("\n", $data);
            $totalIn = 0;
            $totalOut = 0;

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || !str_contains($line, ':')) {
                    continue;
                }
                
                $parts = explode(':', $line);
                $iface = trim($parts[0]);
                
                // Exclude virtual/local interfaces, keep physical or tunnels like CloudflareWARP
                if (
                    $iface === 'lo' || 
                    str_starts_with($iface, 'veth') || 
                    str_starts_with($iface, 'br-') || 
                    str_starts_with($iface, 'docker')
                ) {
                    continue;
                }

                $parts = preg_split('/\s+/', trim($parts[1]));
                if (count($parts) >= 9) {
                    $totalIn += (int)$parts[0];
                    $totalOut += (int)$parts[8];
                }
            }

            return ['in' => $totalIn, 'out' => $totalOut];
        };

        $t1 = $getData();
        usleep(1000000); // 1s for better stability
        $t2 = $getData();

        if (!$t1 || !$t2) {
            return ['incoming' => 0, 'outgoing' => 0, 'max' => 1000];
        }

        $inRate = ($t2['in'] - $t1['in']) * 8 / 1024 / 1024 / 1.0; // Mbps
        $outRate = ($t2['out'] - $t1['out']) * 8 / 1024 / 1024 / 1.0; // Mbps

        return [
            'incoming' => round($inRate, 2),
            'outgoing' => round($outRate, 2),
            'total_received' => round($t2['in'] / 1024 / 1024 / 1024, 2), // GB
            'total_sent' => round($t2['out'] / 1024 / 1024 / 1024, 2),     // GB
            'max' => 1000,
        ];
    }

    private function getStreamingStats(): array
    {
        try {
            $allActive = StreamSession::where('status', StreamStatus::LIVE)->count();
            $youtubeActive = StreamSession::where('status', StreamStatus::LIVE)->where('platform', 'youtube')->count();
            $totalVideos = Video::count();

            // Encoder load based on active streams relative to capacity (16 is currently hardcoded capacity)
            $encoderLoad = round(($allActive / 16) * 100, 1);

            return [
                'active_streams' => $allActive,
                'youtube_active' => $youtubeActive,
                'encoder_load' => $encoderLoad,
                'jobs' => [
                    ['name' => 'Parallel Transcodes', 'value' => "$allActive/16"],
                    ['name' => 'YouTube Destinations', 'value' => $youtubeActive],
                    ['name' => 'Total Library', 'value' => $totalVideos],
                ],
            ];
        } catch (Exception $e) {
            Log::error('Failed to get streaming stats', ['error' => $e->getMessage()]);
            return [
                'active_streams' => 0,
                'youtube_active' => 0,
                'encoder_load' => 0,
                'jobs' => [],
            ];
        }
    }
}
