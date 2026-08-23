<?php

namespace Tests\Unit\Actions\Admin\Email\Fakes;

use App\Models\User;

class FakeCampaignMail
{
    public function __construct(private readonly ?User $user) {}

    public function render(): string
    {
        return 'Hello '.($this->user?->name ?? 'guest');
    }
}
