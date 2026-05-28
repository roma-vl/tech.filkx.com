<?php

namespace App\Api\Admin\Actions;

use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportAdminUsersAction
{
    public function execute(array $filters): StreamedResponse
    {
        $listAction = new ListAdminUsersAction;
        $query = User::query()->with(['subscription.plan']);

        $listAction->applyFilters($query, $filters);

        $users = $query->get();

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Email', 'Plan', 'Status', 'Joined At']);

            foreach ($users as $user) {
                $status = $user->status;
                if ($user->trashed()) {
                    $status = 'deleted';
                }

                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->subscription?->plan?->name ?? 'Free',
                    $status,
                    $user->created_at->toDateString(),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=clients_export.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ]);
    }
}
