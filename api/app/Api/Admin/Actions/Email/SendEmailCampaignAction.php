<?php

namespace App\Api\Admin\Actions\Email;

use App\Jobs\SendCampaignMailJob;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class SendEmailCampaignAction
{
    public function execute(string $campaignClass, string $audience): int
    {
        $usersQuery = $this->getAudienceQuery($audience);
        $users = $usersQuery->get();
        $count = $users->count();

        foreach ($users as $user) {
            SendCampaignMailJob::dispatch($user, $campaignClass);
        }

        return $count;
    }

    private function getAudienceQuery(string $audience): Builder
    {
        $query = User::query();

        switch ($audience) {
            case 'trial_expired':
                $query->whereHas('subscription', function ($q) {
                    $q->where('status', 'expired')
                        ->orWhere(function ($sq) {
                            $sq->where('status', 'trial')->where('ends_at', '<', now());
                        });
                });
                break;
            case 'new_users':
                $query->where('created_at', '>=', now()->subDays(7));
                break;
            case 'active_subscribers':
                $query->whereHas('subscription', function ($q) {
                    $q->whereIn('status', ['active', 'trial'])->where('ends_at', '>', now());
                });
                break;
        }

        return $query;
    }
}
