<?php

namespace App\Api\Admin\Actions\ServerLog;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class ListServerLogsAction
{
    public function execute(): Collection
    {
        $logPath = storage_path('logs');
        $files = File::files($logPath);

        return collect($files)->map(function ($file) {
            return [
                'name' => $file->getFilename(),
                'size' => $file->getSize(),
                'updated_at' => date('Y-m-d H:i:s', $file->getMTime()),
            ];
        })->sortByDesc('updated_at')->values();
    }
}
