<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Api\V1\Exceptions\ProductSlugConflictException;

class BulkRestoreAdminProductsAction
{
    public function __construct(
        protected RestoreAdminProductAction $restoreAction
    ) {}

    /**
     * @param  array<int, int>  $ids
     * @return array{restored: int, failed: array<int, int>}
     */
    public function execute(array $ids): array
    {
        $restored = 0;
        $failed = [];

        foreach ($ids as $id) {
            try {
                $this->restoreAction->execute((int) $id);
                $restored++;
            } catch (ProductNotFoundException|ProductSlugConflictException) {
                $failed[] = (int) $id;
            }
        }

        return ['restored' => $restored, 'failed' => $failed];
    }
}
