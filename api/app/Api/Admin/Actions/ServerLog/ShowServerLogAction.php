<?php

namespace App\Api\Admin\Actions\ServerLog;

use Illuminate\Support\Facades\File;

class ShowServerLogAction
{
    public function execute(string $filename): array
    {
        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $filename)) {
            abort(400, 'Invalid filename');
        }

        $path = storage_path('logs/' . $filename);

        if (!File::exists($path)) {
            abort(404, 'Log file not found');
        }

        $content = $this->tailCustom($path, 5000);

        return [
            'name' => $filename,
            'content' => $content,
        ];
    }

    /**
     * Efficiently read the end of a file
     */
    private function tailCustom(string $filepath, int $lines = 100): string|false
    {
        $f = @fopen($filepath, 'rb');
        if ($f === false) {
            return false;
        }

        $buffer = 4096;
        fseek($f, -1, SEEK_END);
        if (fread($f, 1) != "\n") {
            $lines -= 1;
        }

        $output = '';
        $chunk = '';

        while (ftell($f) > 0 && $lines >= 0) {
            $seek = min(ftell($f), $buffer);
            fseek($f, -$seek, SEEK_CUR);
            $chunk = fread($f, $seek);
            $output = $chunk . $output;
            fseek($f, -$seek, SEEK_CUR);
            $lines -= substr_count($chunk, "\n");
        }

        fclose($f);

        return $output;
    }
}
