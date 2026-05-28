<?php

namespace App\Api\Admin\Actions\ServerLog;

use Illuminate\Support\Facades\File;

class ClearServerLogAction
{
    public function execute(string $filename): void
    {
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            abort(400, 'Invalid filename');
        }

        $path = storage_path('logs/' . $filename);

        if (!File::exists($path)) {
            abort(404, 'Log file not found');
        }

        File::put($path, '');
    }
}
