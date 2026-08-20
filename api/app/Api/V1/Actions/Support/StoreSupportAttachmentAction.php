<?php

namespace App\Api\V1\Actions\Support;

use Illuminate\Http\UploadedFile;

class StoreSupportAttachmentAction
{
    /**
     * @return array{file_path: ?string, file_type: ?string, file_name: ?string, file_size: ?int}
     */
    public function execute(?UploadedFile $file): array
    {
        if (! $file) {
            return [
                'file_path' => null,
                'file_type' => null,
                'file_name' => null,
                'file_size' => null,
            ];
        }

        return [
            'file_path' => $file->store('support_files', 'public'),
            'file_type' => $file->getClientMimeType(),
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ];
    }
}
