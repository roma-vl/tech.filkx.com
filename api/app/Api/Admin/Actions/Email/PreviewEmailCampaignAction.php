<?php

namespace App\Api\Admin\Actions\Email;

use Illuminate\Support\Facades\Auth;

class PreviewEmailCampaignAction
{
    public function execute(string $campaignClass): string
    {
        if (! class_exists($campaignClass)) {
            throw new \InvalidArgumentException('Invalid campaign class.');
        }

        $user = Auth::user();

        return (new $campaignClass($user))->render();
    }
}
